<?php

namespace App\Controller;

use App\Repository\PanierproduitRepository;
use App\Repository\PanierRepository;
use App\Service\StripeService;
use App\Service\TwilioService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class PaiementProduitController extends AbstractController
{
    private StripeService $stripeService;
    private Security $security;
    private LoggerInterface $logger;
    private TwilioService $twilioService;

    public function __construct(StripeService $stripeService, Security $security, LoggerInterface $logger, TwilioService $twilioService)
    {
        $this->stripeService = $stripeService;
        $this->security = $security;
        $this->logger = $logger;
        $this->twilioService = $twilioService;
    }

    #[Route('/panier/payer', name: 'panier_payer', methods: ["GET"])]
    public function payerPanier(PanierproduitRepository $panierproduitRepo, PanierRepository $panierRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter pour accéder au paiement.');
            return $this->redirectToRoute('app_login');
        }

        $panier = $panierRepo->findPanierByUser($user);
        if (!$panier) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_afficher');
        }

        $items = $panierproduitRepo->findBy(['panier' => $panier]);
        $total = 0;
        foreach ($items as $it) {
            $total += $it->getQuantite() * $it->getProduit()->getPrix();
        }

        $publicKey = $this->getParameter('stripe.public_key');
        $this->logger->info('Clé publique Stripe utilisée : ' . $publicKey);

        return $this->render('panier/paiementProduit.html.twig', [
            'items' => $items,
            'total' => $total,
            'publicKey' => $publicKey,
        ]);
    }

    #[Route('/panier/create-payment-intent', name: 'panier_create_payment_intent', methods: ["POST"])]
    public function createPaymentIntent(Request $request, PanierproduitRepository $panierproduitRepo, PanierRepository $panierRepo): JsonResponse
    {
        $data = $request->toArray();
        $phone = $data['phone'] ?? null;
        $this->logger->info('createPaymentIntent - phone reçu : ' . ($phone ?? 'null'));

        if (!$phone) {
            return new JsonResponse(['error' => 'Numéro de téléphone manquant'], 400);
        }

        $user = $this->getUser();
        $panier = $panierRepo->findPanierByUser($user);
        if (!$panier) {
            return new JsonResponse(['error' => 'Panier vide'], 400);
        }

        $items = $panierproduitRepo->findBy(['panier' => $panier]);
        $amount = 0;
        foreach ($items as $it) {
            $amount += $it->getQuantite() * $it->getProduit()->getPrix() * 100;
        }

        try {
            $paymentIntent = $this->stripeService->createPaymentIntent(
                (int)$amount,
                'eur',
                ['userId' => $user->getId(), 'phone' => $phone]
            );
            return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur création PaymentIntent : ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/paiement/success', name: 'paiement_success', methods: ['GET'])]
    public function success(Request $request, PanierproduitRepository $panierProduitRepo, PanierRepository $panierRepo, EntityManagerInterface $entityManager): Response
    {
        $piId = $request->query->get('payment_intent');
        if (!$piId) {
            $this->logger->error('ID du PaymentIntent manquant dans la requête.');
            $this->addFlash('error', 'ID du PaymentIntent manquant.');
            return $this->redirectToRoute('app_home');
        }

        try {
            $pi = $this->stripeService->retrievePaymentIntent($piId);
            if ($pi->status !== 'succeeded') {
                $this->logger->warning('Statut du paiement non réussi : ' . $pi->status, [
                    'payment_intent' => $piId,
                ]);
                $this->addFlash('error', 'Le paiement n’a pas été validé.');
                return $this->redirectToRoute('app_afficher_panier');
            }

            $user = $this->getUser();
            $panier = $panierRepo->findPanierByUser($user);
            if (!$panier) {
                $this->logger->error('Aucun panier trouvé pour l’utilisateur : ' . $user->getId(), [
                    'payment_intent' => $piId,
                ]);
                $this->addFlash('error', 'Aucun panier trouvé.');
                return $this->redirectToRoute('app_afficher_panier');
            }

            $items = $panierProduitRepo->findBy(['panier' => $panier]);
            $total = 0;
            foreach ($items as $it) {
                $qty = $it->getQuantite();
                $prix = $it->getProduit()->getPrix();
                $sub = $qty * $prix;
                $total += $sub;
                $it->setEtatPaiement('Payé');

                $produit = $it->getProduit();
                $currentStock = $produit->getQuantite();
                if ($currentStock < $qty) {
                    $this->logger->error('Stock insuffisant pour : ' . $produit->getNom() . ' (Requis : ' . $qty . ', Disponible : ' . $currentStock . ')', [
                        'payment_intent' => $piId,
                        'user_id' => $user->getId(),
                    ]);
                    $this->addFlash('error', 'Stock insuffisant pour : ' . $produit->getNom());
                    return $this->redirectToRoute('app_afficher_panier');
                }
                $produit->setQuantite($currentStock - $qty);
                $entityManager->persist($produit);
            }

            $entityManager->flush();

            // Use default phone number if not provided
            $phone = $pi->metadata['phone'] ?? '+21621542305';
            $this->logger->info('Phone number used: ' . $phone);

            $smsSent = false;
            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                $parsedNumber = $phoneUtil->parse($phone, null);
                if ($phoneUtil->isValidNumber($parsedNumber)) {
                    $phone = $phoneUtil->format($parsedNumber, \libphonenumber\PhoneNumberFormat::E164);
                    $this->logger->info('Numéro de téléphone valide pour envoi SMS : ' . $phone, [
                        'payment_intent' => $piId,
                        'user_id' => $user->getId(),
                    ]);

                    $receiveSms = method_exists($user, 'isReceiveSms') ? $user->isReceiveSms() : true;
                    if ($receiveSms) {
                        $message = $this->buildOrderConfirmationMessage($items, $total);
                        try {
                            $messageSid = $this->twilioService->sendSms($phone, $message);
                            $this->logger->info('SMS envoyé avec succès via Twilio', [
                                'sid' => $messageSid,
                                'user_id' => $user->getId(),
                                'payment_intent' => $piId,
                                'phone' => $phone,
                            ]);
                            if (method_exists($panier, 'setSmsSid')) {
                                $panier->setSmsSid($messageSid);
                                $entityManager->persist($panier);
                                $entityManager->flush();
                            }
                            $smsSent = true;
                        } catch (\Exception $e) {
                            $this->logger->error('Échec de l’envoi SMS : ' . $e->getMessage(), [
                                'user_id' => $user->getId(),
                                'payment_intent' => $piId,
                                'phone' => $phone,
                                'error_code' => $e->getCode(),
                            ]);
                            $this->addFlash('warning', 'Erreur lors de l’envoi du SMS.');
                        }
                    } else {
                        $this->logger->info('SMS non envoyé : utilisateur a désactivé les notifications SMS', [
                            'user_id' => $user->getId(),
                            'payment_intent' => $piId,
                            'phone' => $phone,
                        ]);
                        $this->addFlash('warning', 'SMS non envoyé : notifications SMS désactivées.');
                    }
                } else {
                    $this->logger->warning('Numéro de téléphone invalide : ' . $phone, [
                        'payment_intent' => $piId,
                        'user_id' => $user->getId(),
                    ]);
                    $this->addFlash('warning', 'Numéro de téléphone invalide ; SMS non envoyé.');
                }
            } catch (NumberParseException $e) {
                $this->logger->warning('Erreur de parsing du numéro : ' . $phone . ' - ' . $e->getMessage(), [
                    'payment_intent' => $piId,
                    'user_id' => $user->getId(),
                ]);
                $this->addFlash('warning', 'Numéro de téléphone invalide ; SMS non envoyé.');
            }

            $this->addFlash('success', 'Paiement validé' . ($smsSent ? ' et SMS envoyé !' : ' !'));
            return $this->redirectToRoute('app_afficher_panier');
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la validation du paiement : ' . $e->getMessage(), [
                'payment_intent' => $piId,
                'user_id' => $user->getId(),
            ]);
            $this->addFlash('error', 'Erreur validation paiement : ' . $e->getMessage());
            return $this->redirectToRoute('app_afficher_panier');
        }
    }

    #[Route('/paiement/cancel', name: 'paiement_cancel', methods: ["GET"])]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_afficher_panier');
    }

    private function buildOrderConfirmationMessage(array $items, float $total): string
    {
        $lines = ["✅ Votre commande a été payée :"];
        foreach ($items as $it) {
            $qty = $it->getQuantite();
            $prix = $it->getProduit()->getPrix();
            $sub = $qty * $prix;
            $lines[] = sprintf("%s x%d = %s DT", $it->getProduit()->getNom(), $qty, number_format($sub, 2));
        }
        $lines[] = sprintf("Total : %s DT", number_format($total, 2));
        $lines[] = "Le : " . (new \DateTime())->format('d/m/Y H:i');
        return implode("\n", $lines);
    }
}
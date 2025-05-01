<?php

namespace App\Controller;

use App\Repository\PanierproduitRepository;
use App\Repository\PanierRepository;
use App\Service\SmsSender;
use App\Service\StripeService;
use App\Service\TwilioService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PaiementProduitController extends AbstractController
{
    private StripeService $stripeService;
    private Security $security;
    private LoggerInterface $logger;

    public function __construct(StripeService $stripeService, Security $security, LoggerInterface $logger)
    {
        $this->stripeService = $stripeService;
        $this->security      = $security;
        $this->logger        = $logger;
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
    public function createPaymentIntent(Request $request, PanierproduitRepository $panierproduitpRepo, PanierRepository $panierRepo, LoggerInterface $logger): JsonResponse
    {
        // Récupérer le JSON envoyé par JS
        $data = $request->toArray();
        $phone = $data['phone'] ?? null;
        $logger->info('createPaymentIntent - phone reçu : ' . $phone);

        $user = $this->getUser();
        $panier = $panierRepo->findPanierByUser($user);
        if (!$panier) {
            return new JsonResponse(['error' => 'Panier vide'], 400);
        }

        $items = $panierproduitpRepo->findBy(['panier' => $panier]);

        $amount = 0;
        foreach ($items as $it) {
            $amount += $it->getQuantite() * $it->getProduit()->getPrix() * 100;
        }

        try {
            $paymentIntent = $this->stripeService->createPaymentIntent(
                (int)$amount,
                'eur',
                [
                    'userId' => $user->getId(),
                    'phone' => $phone // Store the phone number in metadata
                ]
            );
            return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur création PaymentIntent : ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
//
//    #[Route('/paiement/success', name: 'paiement_success', methods: ['GET'])]
//    public function success(Request $request, PanierproduitRepository $panierProduitRepo, PanierRepository $panierRepo, EntityManagerInterface $entityManager, NotifierInterface $notifier, TwilioService $twilioService,SmsSender $smsSender): Response
//    {
//        $piId = $request->query->get('payment_intent');
//        if (!$piId) {
//            $this->addFlash('error', 'ID du PaymentIntent manquant.');
//            return $this->redirectToRoute('app_home');
//        }
//
//        try {
//            // Vérifier le statut du PaymentIntent
//            $pi = $this->stripeService->retrievePaymentIntent($piId);
//            if ($pi->status !== 'succeeded') {
//                $this->addFlash('error', 'Le paiement n’a pas été validé.');
//                return $this->redirectToRoute('app_afficher_panier');
//            }
//
//            // Récupérer le numéro de téléphone depuis les métadonnées
//            $phone = $pi->metadata['phone'] ?? null;
//            if (!$phone) {
//                $this->logger->error('Numéro de téléphone non trouvé dans les métadonnées du PaymentIntent.');
//                $this->addFlash('warning', 'Numéro de téléphone manquant dans les métadonnées.');
//            } else {
//                $this->logger->info('Numéro de téléphone utilisé pour l’envoi du SMS : ' . $phone);
//            }
//
//            $user = $this->getUser();
//            $panier = $panierRepo->findPanierByUser($user);
//            if (!$panier) {
//                $this->addFlash('error', 'Aucun panier trouvé.');
//                return $this->redirectToRoute('app_afficher_panier');
//            }
//
//            $items = $panierProduitRepo->findBy(['panier' => $panier]);
//            $total = 0;
//            $lines = ["✅ Votre commande a été payée :"];
//            foreach ($items as $it) {
//                $qty = $it->getQuantite();
//                $prix = $it->getProduit()->getPrix();
//                $sub = $qty * $prix;
//                $total += $sub;
//                $it->setEtatPaiement('Payé');
//
//                // Mise à jour du stock du produit
//                $produit = $it->getProduit();
//                $currentStock = $produit->getQuantite();
//                if ($currentStock < $qty) {
//                    $this->logger->error('Stock insuffisant pour le produit : ' . $produit->getNom());
//                    $this->addFlash('error', 'Stock insuffisant pour le produit : ' . $produit->getNom());
//                    return $this->redirectToRoute('app_afficher_panier');
//                }
//                $produit->setQuantite($currentStock - $qty);
//                $entityManager->persist($produit);
//
//                $lines[] = sprintf("%s x%d = %s DT", $it->getProduit()->getNom(), $qty, number_format($sub, 2));
//            }
//            $lines[] = sprintf("Total : %s DT", number_format($total, 2));
//            $lines[] = "Le : " . (new \DateTime())->format('d/m/Y H:i');
//
//            $entityManager->flush();
//              }
//            try {
//                // Ajouter le préfixe international (ex. +33 pour la France)
//                if (!str_starts_with($phone, '+')) {
//                    $phone = '+216' . $phone; // Ajuste selon le pays
//                }
//
//                // Vérifier que getNom() existe, sinon utiliser une valeur par défaut
//                $nom = method_exists($user, 'getNom') ? $user->getNom() : 'Utilisateur';
//                $message = sprintf('Bonjour %s, votre inscription a été validée, vous etes officiellement membre de la communauté Coachini ! Connectez-vous sur notre plateforme.', $nom);
//                $smsSent = $smsSender->sendSms($phone, $message);
//
//                if (!$smsSent) {
//                    $this->addFlash('warning', 'Utilisateur validé, mais le SMS n\'a pas pu être envoyé.');
//                }
//            } catch (\Exception $e) {
//                $this->addFlash('warning', 'Utilisateur validé, mais une erreur est survenue lors de l\'envoi du SMS : ' . $e->getMessage());
//
//
//             else {
//                $this->addFlash('warning', 'Aucun numéro de téléphone fourni ; pas de SMS envoyé.');
//            }
//
//
//        } catch (\Exception $e) {
//            $this->addFlash('error', 'Erreur lors de la validation du paiement : ' . $e->getMessage());
//            return $this->redirectToRoute('app_afficher_panier');
//        }
//


    #[Route('/paiement/success', name: 'paiement_success', methods: ['GET'])]
    public function success(
        Request $request,
        PanierproduitRepository $panierProduitRepo,
        PanierRepository $panierRepo,
        EntityManagerInterface $entityManager,
        NotifierInterface $notifier,
        TwilioService $twilioService,
        SmsSender $smsSender
    ): Response {
        $piId = $request->query->get('payment_intent');
        if (!$piId) {
            $this->addFlash('error', 'ID du PaymentIntent manquant.');
            return $this->redirectToRoute('app_home');
        }

        try {
            // Vérifier le statut du PaymentIntent
            $pi = $this->stripeService->retrievePaymentIntent($piId);
            if ($pi->status !== 'succeeded') {
                $this->addFlash('error', 'Le paiement n’a pas été validé.');
                return $this->redirectToRoute('app_afficher_panier');
            }

            // Récupérer le numéro de téléphone depuis les métadonnées
            $phone = $pi->metadata['phone'] ?? null;
            if (!$phone) {
                $this->logger->error('Numéro de téléphone non trouvé dans les métadonnées du PaymentIntent.');
                $this->addFlash('warning', 'Numéro de téléphone manquant dans les métadonnées.');
            } else {
                $this->logger->info('Numéro de téléphone utilisé pour l’envoi du SMS : ' . $phone);
            }

            $user = $this->getUser();
            $panier = $panierRepo->findPanierByUser($user);
            if (!$panier) {
                $this->addFlash('error', 'Aucun panier trouvé.');
                return $this->redirectToRoute('app_afficher_panier');
            }

            $items = $panierProduitRepo->findBy(['panier' => $panier]);
            $total = 0;
            $lines = ["✅ Votre commande a été payée :"];
            foreach ($items as $it) {
                $qty = $it->getQuantite();
                $prix = $it->getProduit()->getPrix();
                $sub = $qty * $prix;
                $total += $sub;
                $it->setEtatPaiement('Payé');

                // Mise à jour du stock du produit
                $produit = $it->getProduit();
                $currentStock = $produit->getQuantite();
                if ($currentStock < $qty) {
                    $this->logger->error('Stock insuffisant pour le produit : ' . $produit->getNom());
                    $this->addFlash('error', 'Stock insuffisant pour le produit : ' . $produit->getNom());
                    return $this->redirectToRoute('app_afficher_panier');
                }
                $produit->setQuantite($currentStock - $qty);
                $entityManager->persist($produit);

                $lines[] = sprintf("%s x%d = %s DT", $produit->getNom(), $qty, number_format($sub, 2));
            }

            $lines[] = sprintf("Total : %s DT", number_format($total, 2));
            $lines[] = "Le : " . (new \DateTime())->format('d/m/Y H:i');
            $entityManager->flush();

            // Envoi du SMS si le numéro est disponible
            if ($phone) {
                try {
                    // Ajouter le préfixe international si manquant
                    if (!str_starts_with($phone, '+')) {
                        $phone = '+216' . $phone; // Ajuster selon votre pays
                    }

                    $nom = method_exists($user, 'getNom') ? $user->getNom() : 'Utilisateur';
                    $message = sprintf(
                        'Bonjour %s, votre inscription a été validée, vous êtes officiellement membre de la communauté Coachini ! Connectez-vous sur notre plateforme.',
                        $nom
                    );

                    $smsSent = $smsSender->sendSms($phone, $message);
                    if (!$smsSent) {
                        $this->addFlash('warning', 'Utilisateur validé, mais le SMS n\'a pas pu être envoyé.');
                    }
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Utilisateur validé, mais une erreur est survenue lors de l\'envoi du SMS : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('warning', 'Aucun numéro de téléphone fourni ; pas de SMS envoyé.');
            }

            $this->addFlash('success', 'Paiement validé avec succès !');
            return $this->redirectToRoute('app_home');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la validation du paiement : ' . $e->getMessage());
            return $this->redirectToRoute('app_afficher_panier');
        }
    }

    #[Route('/paiement/cancel', name: 'paiement_cancel', methods: ["GET"])]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_afficher_panier');
    }
}

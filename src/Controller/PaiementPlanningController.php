<?php

namespace App\Controller;

use App\Entity\PaiementPlanning;
use App\Repository\CoachRepository;
use App\Repository\PlanningRepository;
use App\Service\StripeService;
use Google\Service\Compute\Address;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Mailjet\Client as MailjetClient;
use Mailjet\Resources;
class PaiementPlanningController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private StripeService $stripeService;
    private Security $security;
    private LoggerInterface $logger;
    private $mailjet;
    public function __construct(EntityManagerInterface $entityManager,MailjetClient $mailjet, StripeService $stripeService, Security $security, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->stripeService = $stripeService;
        $this->security = $security;
        $this->logger = $logger;
        $this->mailjet = $mailjet;
    }


    #[Route('/payer/coach/{id}', name: 'payer_coach')]
    public function payerCoach($id, CoachRepository $coachRepository, PlanningRepository $planningRepository): Response
    {
        // Vérifier si un utilisateur est connecté
        if (!$this->security->getUser()) {
            $this->addFlash('error', 'Veuillez vous connecter pour effectuer un paiement.');
            return $this->redirectToRoute('app_login');
        }

        $coach = $coachRepository->find($id);

        if (!$coach) {
            throw $this->createNotFoundException('Coach non trouvé.');
        }

        $planning = $planningRepository->findOneBy(['coach' => $id]);

        // Débogage : Vérifier que la clé publique est bien récupérée
        $stripePublicKey = $this->getParameter('stripe.public_key'); // Changement de stripe_public_key à stripe.public_key
        $this->logger->info('Clé publique Stripe chargée: ' . $stripePublicKey);

        return $this->render('planning/payer_coach.html.twig', [
            'coach' => $coach,
            'planning' => $planning,
            'stripe_public_key' => $stripePublicKey,
        ]);
    }

    #[Route('/create-payment-intent/{planningId}', name: 'create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent($planningId, PlanningRepository $planningRepository): JsonResponse
    {
        $planning = $planningRepository->find($planningId);
        if (!is_numeric($planningId)) {
            return new JsonResponse(['error' => 'ID de planning invalide.'], 400);
        }

        if (!$planning) {
            return new JsonResponse(['error' => 'Planning non trouvé.'], 404);
        }

        $loggedInUser = $this->security->getUser();
        if (!$loggedInUser) {
            return new JsonResponse(['error' => 'Utilisateur non connecté.'], 403);
        }

        try {
            $paymentIntent = $this->stripeService->createPaymentIntent(
                $planning->getTarif(),
                'usd',
                ['planningId' => $planningId, 'adherentId' => $loggedInUser->getId()]
            );

            return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/paiement/planing/success', name: 'paiement_successs')]
    public function success(Request $request, PlanningRepository $planningRepository,MailerInterface $mailer): Response
    {
        $paymentIntentId = $request->query->get('payment_intent');
        if (!$paymentIntentId) {
            $this->addFlash('error', 'Erreur : Payment Intent ID manquant.');
            return $this->redirectToRoute('app_homee');
        }

        $paymentIntent = $this->stripeService->retrievePaymentIntent($paymentIntentId);
        if ($paymentIntent->status !== 'succeeded') {
            $this->addFlash('error', 'Erreur : Paiement non validé.');
            return $this->redirectToRoute('app_homee');
        }

        $planningId = $paymentIntent->metadata['planningId'] ?? null;
        $adherentId = $paymentIntent->metadata['adherentId'] ?? null;

        $planning = $planningRepository->find($planningId);
        if (!$planning) {
            $this->addFlash('error', 'Erreur : Planning non trouvé.');
            return $this->redirectToRoute('app_homee');
        }

        $adherent = $this->getUser();
        if (!$adherent || $adherent->getId() !== (int)$adherentId) {
            $this->addFlash('error', 'Erreur : Adhérent non connecté ou ID incorrect.');
            return $this->redirectToRoute('app_homee');
        }

        $paiement = new PaiementPlanning();
        $paiement->setAdherent($adherent);
        $paiement->setPlanning($planning);
        $paiement->setEtatPaiement('PAYE');
        $paiement->setDatePaiement(new \DateTime());

        $this->entityManager->persist($paiement);
        $this->entityManager->flush();
        $datePaiement = $paiement->getDatePaiement()->format('d/m/Y H:i');

//        $email = (new Email())
//            ->from('Coachini <maissa.maalej3@gmail.com>')   // adresse de ton compte Mailjet
//            ->to($adherent->getEmail())           // adresse de l'adhérent
//            ->subject('Confirmation de paiement')
//            ->html("
//            <p>Bonjour {$adherent->getNom()},</p>
//            <p>Votre paiement pour le planning <strong>{$planning->getTitre()}</strong> a été effectué avec succès.</p>
//            <h3>Détails du paiement :</h3>
//            <ul>
//                <li><strong>Planning :</strong> {$planning->getTitre()}</li>
//                <li><strong>Tarif :</strong> {$planning->getTarif()} \$</li>
//                <li><strong>Date de paiement :</strong> " . (new \DateTime())->format('d/m/Y H:i') . "</li>
//            </ul>
//            <p>Merci pour votre confiance !</p>
//            <p><strong>L'équipe Coachini</strong></p>
//        ");

//        $mailer->send($email);
        try {
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => 'farahbenyedderr@gmail.com',
                            'Name' => 'Coachini',
                        ],
                        'To' => [
                            [
                                'Email' => $adherent->getEmail(),
                            ],
                        ],
                        'TemplateID' => 6765931,
                        'TemplateLanguage' => true,
                        'Subject' => 'Confirmation de paiement',
                        'Variables' => [
                            'CODE' => 'hhhhhhhhh353333hhhh',
                        ],
                    ],
                ],
            ];

            $response = $this->mailjet->post(Resources::$Email, ['body' => $body]);
            if (!$response->success()) {
                $this->addFlash('success', 'Paiement effectué avec succès ! Merci pour votre confiance.');
            }
        } catch (\Exception $e) {
            return $this->json(['error' => 'Échec de l’envoi de l’e-mail : ' . $e->getMessage()], 500);
        }


        return $this->redirectToRoute('app_homee');
    }


}
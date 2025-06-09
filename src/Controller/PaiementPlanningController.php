<?php

namespace App\Controller;

use App\Entity\PaiementPlanning;
use App\Repository\CoachRepository;
use App\Repository\PlanningRepository;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Mailjet\Client as MailjetClient;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Mailjet\Resources;

class PaiementPlanningController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private StripeService $stripeService;
    private Security $security;
    private LoggerInterface $logger;
    private MailjetClient $mailjet;

    public function __construct(EntityManagerInterface $entityManager, StripeService $stripeService, Security $security, LoggerInterface $logger,MailjetClient $mailjet)
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
        $user = $this->security->getUser();
        if (!$user) {
            $this->logger->warning('No logged-in user for payerCoach, redirecting to login.');
            $this->addFlash('error', 'Veuillez vous connecter pour effectuer un paiement.');
            return $this->redirectToRoute('app_login');
        }

        $coach = $coachRepository->find($id);
        if (!$coach) {
            $this->logger->error("Coach not found for ID: $id");
            throw $this->createNotFoundException('Coach non trouvé.');
        }

        $planning = $planningRepository->findOneBy(['coach' => $id]);
        if (!$planning) {
            $this->logger->warning("No valid planning found for coach ID: $id");
            $this->addFlash('warning', 'Aucun planning valide disponible pour ce coach.');
            return $this->redirectToRoute('app_homee');
        }

        $stripePublicKey = $this->getParameter('stripe.public_key');

        return $this->render('planning/payer_coach.html.twig', [
            'coach' => $coach,
            'planning' => $planning,
            'stripe_public_key' => $stripePublicKey,
        ]);
    }

    #[Route('/create-payment-intent/{planningId}', name: 'create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent($planningId, PlanningRepository $planningRepository): JsonResponse
    {
        // 1. Validation de l'ID
        if (!is_numeric($planningId) || $planningId <= 0) {
            return new JsonResponse(['error' => 'ID de planning invalide.'], 400);
        }
        $planning = $planningRepository->find($planningId);
        if (!$planning) {
            return new JsonResponse(['error' => 'Planning non trouvé.'], 404);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non connecté.'], 403);
        }

        // 2. Création du PaymentIntent avec metadata
        $paymentIntent = $this->stripeService->createPaymentIntent(
            $planning->getTarif(),
            'usd',
            [
                'planning_id' => $planningId,
                'adherent_id' => $user->getId(),
            ]
        );

        return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);
    }


    #[Route('/paiement/planning/success', name: 'paiement_successs')]
    public function success(Request $request, PlanningRepository $planningRepository, MailerInterface $mailer): Response
    {
        $paymentIntentId = $request->query->get('payment_intent');
        if (!$paymentIntentId) {
            $this->addFlash('error', 'Erreur : Payment Intent ID manquant.');
            return $this->redirectToRoute('app_homee');
        }

        try {
            $paymentIntent = $this->stripeService->retrievePaymentIntent($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                $this->addFlash('error', 'Erreur : Paiement non validé.');
                return $this->redirectToRoute('app_homee');
            }

            // DEBUG (optionnel) : journaliser toute la métadonnée
            $this->logger->info('Stripe metadata:', (array)$paymentIntent->metadata);

            // On récupère désormais planning_id
            $planningId = $paymentIntent->metadata['planning_id'] ?? null;
            if (!is_numeric($planningId)) {
                $this->addFlash('error', 'Erreur : ID de planning invalide.');
                return $this->redirectToRoute('app_homee');
            }

            $planning = $planningRepository->find($planningId);
            if (!$planning) {
                $this->addFlash('error', 'Erreur : Planning non trouvé.');
                return $this->redirectToRoute('app_homee');
            }

            $adherent = $this->getUser();
            if (!$adherent) {
                return $this->redirectToRoute('app_login');
            }

            // Enregistrement du paiement
            $paiement = new PaiementPlanning();
            $paiement->setAdherent($adherent);
            $paiement->setPlanning($planning);
            $paiement->setEtatPaiement('PAYE');
            $paiement->setDatePaiement(new \DateTime());

            $this->entityManager->persist($paiement);
            $this->entityManager->flush();

            // Envoi d’email (inchangé)
            $this->sendConfirmationEmail( $adherent, $planning, $paiement->getDatePaiement());

            $this->addFlash('success', 'Paiement effectué avec succès ! Merci pour votre confiance.');
            return $this->redirectToRoute('app_homee');

        } catch (\Exception $e) {
            $this->logger->error('Error in success action: ' . $e->getMessage());
            $this->addFlash('error', 'Une erreur est survenue lors du traitement du paiement.');
            return $this->redirectToRoute('app_homee');
        }
    }

    private function sendConfirmationEmail($adherent, $planning, $datePaiement)
    {
        try {
            // Préparez le corps de la requête Mailjet
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => 'farahbenyedderr@gmail.com',
                            'Name'  => 'Coachini',
                        ],
                        'To' => [
                            [
                                'Email' => $adherent->getEmail(),
                                'Name'  => $adherent->getNom(),
                            ],
                        ],
                        'Subject'  => 'Confirmation de votre paiement Coachini',
                        'TextPart' => sprintf(
                            "Bonjour %s,\n\nVotre paiement pour le planning \"%s\" a été effectué avec succès le %s.\nTarif : %s $\n\nMerci pour votre confiance !",
                            $adherent->getNom(),
                            $planning->getTitre(),
                            $datePaiement->format('d/m/Y H:i'),
                            $planning->getTarif()
                        ),
                        'HTMLPart' => sprintf(
                            "<h3>Bonjour %s,</h3>
                        <p>Votre paiement pour le planning <strong>%s</strong> a été effectué avec succès le <strong>%s</strong>.</p>
                        <ul>
                          <li><strong>Date :</strong> %s</li>
                          <li><strong>Tarif :</strong> %s $</li>
                        </ul>
                        <p>Merci pour votre confiance !</p>",
                            $adherent->getNom(),
                            $planning->getTitre(),
                            $datePaiement->format('d/m/Y H:i'),
                            $datePaiement->format('d/m/Y H:i'),
                            $planning->getTarif()
                        ),
                        'CustomID' => 'ConfirmationPaiementCoachini',
                    ],
                ],
            ];

            $response = $this->mailjet->post(Resources::$Email, ['body' => $body]);

            if (!$response->success()) {
                $this->logger->error(
                    'Échec de l’envoi de l’e-mail via Mailjet.',
                    ['response' => $response->getData()]
                );
            } else {
                $this->logger->info("Email de confirmation envoyé à {$adherent->getEmail()} via Mailjet.");
            }
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'envoi de l'email via Mailjet : " . $e->getMessage());
        }
    }

    #[Route('/paiement/cancel', name: 'paiement_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_homee');
    }
}

<?php

namespace App\Controller;

use App\Entity\Evenement;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Participantevenement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Mailjet\Client as MailjetClient;
use Mailjet\Resources;
use App\Repository\UserRepository;

final class ParticipantEvenementController extends AbstractController{




//
//
//    #[Route('/evenement/participer/{evenementId}', name: 'app_participer_evenement')]
//    public function participerEvenement(
//        int $evenementId,
//        EntityManagerInterface $entityManager,
//        MailerInterface $mailer // Use the injected mailer
//    ): Response
//    {
//        $user = $this->getUser();
//        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);
//
//        if (!$evenement) {
//            $this->addFlash('error', 'Événement non trouvé.');
//            return $this->redirectToRoute('app_events');
//        }
//
//        // Check existing participation
//        $existingParticipation = $entityManager->getRepository(Participantevenement::class)->findOneBy([
//            'user' => $user,
//            'evenement' => $evenement,
//        ]);
//
//        if ($existingParticipation) {
//            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
//            return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
//        }
//
//        // Register participation
//        $participant = new Participantevenement();
//        $participant->setUser($user);
//        $participant->setEvenement($evenement);
//        $participant->setDateInscription(new \DateTime());
//
//        $entityManager->persist($participant);
//        $entityManager->flush();
//
//
//
//            // Send email
//        $email = (new Email())
//
//            ->from('houssemm.labidi@gmail.com')
//
//            ->to($user->getEmail())
//           //->to('farah.benyedder@esprit.tn')
//            ->subject('Confirmation d\'inscription à l\'événement')
//            ->html("
//            <h2>Bonjour {$user->getNom()},</h2>
//            <p>Vous êtes bien inscrit à l'événement <strong>{$evenement->getTitre()}</strong>.</p>
//            <p>Date Début: {$evenement->getDateDebut()->format('d/m/Y')}</p>
//            <p>Statut de paiement : EN ATTENTE</p>
//            <br><p>Merci pour votre confiance !</p>
//        ");
//
//            try {
//           $mailer->send($email);
//                $this->addFlash('success', 'Inscription réussie et e-mail de confirmation envoyé ! ');
//            } catch (\Throwable $e) {
//                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'e-mail : ' . $e->getMessage());
//            }
//
//            return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
//        }
//
//




    #[Route('/evenement/participer/{evenementId}', name: 'app_participer_evenement')]
    public function participerEvenement(
        int $evenementId,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response
    {
        $user = $this->getUser();
        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);

        if (!$evenement) {
            $this->addFlash('error', 'Événement non trouvé.');
            return $this->redirectToRoute('app_events');
        }

        $existingParticipation = $entityManager->getRepository(Participantevenement::class)->findOneBy([
            'user' => $user,
            'evenement' => $evenement,
        ]);

        if ($existingParticipation) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
            return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
        }

        $participant = new Participantevenement();
        $participant->setUser($user);
        $participant->setEvenement($evenement);
        $participant->setDateInscription(new \DateTime());

        $entityManager->persist($participant);
        $entityManager->flush();

        // Log recipient email for debugging
        $recipientEmail = $user->getEmail();
        error_log("Sending email to: $recipientEmail");

        $email = (new Email())
            ->from('houssemm.labidi@gmail.com')
            ->to('houssemm.labidi@gmail.com')
            ->subject('Confirmation d\'inscription à l\'événement')
            ->html("
            <h2>Bonjour {$user->getNom()},</h2>
            <p>Vous êtes bien inscrit à l'événement <strong>{$evenement->getTitre()}</strong>.</p>
            <p>Date Début: {$evenement->getDateDebut()->format('d/m/Y')}</p>
            <p>Statut de paiement : EN ATTENTE</p>
            <br><p>Merci pour votre confiance !</p>
        ");

        try {
            $mailer->send($email);
            $this->addFlash('success', 'Inscription réussie et e-mail de confirmation envoyé !');
        } catch (\Throwable $e) {
            error_log("Email sending failed: " . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'e-mail : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
    }
    #[Route('/create-checkout-session/{evenementId}', name: 'app_create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(
        int $evenementId,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        #[Autowire('%env(STRIPE_SECRET_KEY)%')] string $stripeSecretKey,
        #[Autowire('%env(STRIPE_CURRENCY)%')] string $stripeCurrency // Inject the currency
    ): RedirectResponse
    {
        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);
        $user = $this->getUser();

        if (!$evenement || !$user) {
            $this->addFlash('error', 'Événement non trouvé ou utilisateur non authentifié.');
            return $this->redirectToRoute('app_events');
        }

        Stripe::setApiKey($stripeSecretKey);

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $stripeCurrency, // Use the configured currency
                    'unit_amount' => $evenement->getPrix() * 100, // Assuming event price is in TND, this will charge in EUR cents
                    'product_data' => [
                        'name' => $evenement->getTitre(),
                        'description' => $evenement->getDescription(),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $urlGenerator->generate('app_payment_success', ['evenementId' => $evenementId], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $urlGenerator->generate('app_event_details', ['id' => $evenementId], UrlGeneratorInterface::ABSOLUTE_URL),
            'customer_email' => $user->getEmail(),
            'metadata' => [
                'evenementId' => $evenementId,
                'userId' => $user->getId(),
            ],
        ]);

        return new RedirectResponse($checkoutSession->url, 303);
    }

    #[Route('/payment/success/{evenementId}', name: 'app_payment_success')]
    public function paymentSuccess(int $evenementId, EntityManagerInterface $entityManager, #[Autowire('%env(STRIPE_CURRENCY)%')] string $stripeCurrency): Response
    {
        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);
        $user = $this->getUser();

        if (!$evenement || !$user) {
            $this->addFlash('error', 'Événement non trouvé ou utilisateur non authentifié.');
            return $this->redirectToRoute('app_events');
        }

        // Check if the user is already registered
        $existingParticipation = $entityManager->getRepository(Participantevenement::class)->findOneBy([
            'user' => $user,
            'evenement' => $evenement,
        ]);

        if (!$existingParticipation) {
            $participant = new Participantevenement();
            $participant->setUser($user);
            $participant->setEvenement($evenement);
            $participant->setDateInscription(new \DateTime());
            $participant->setEtatPaiement('PAYÉ');

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Paiement réussi ! Vous êtes inscrit à l\'événement (paiement en ' . strtoupper($stripeCurrency) . ').');
        } else {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
        }

        return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
    }

    #[Route('/payment/cancel/{evenementId}', name: 'app_payment_cancel')]
    public function paymentCancel(int $evenementId, #[Autowire('%env(STRIPE_CURRENCY)%')] string $stripeCurrency): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé (tentative en ' . strtoupper($stripeCurrency) . ').');
        return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
    }

    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request, EntityManagerInterface $entityManager, #[Autowire('%env(STRIPE_SECRET_KEY)%')] string $stripeSecretKey, #[Autowire('%env(STRIPE_WEBHOOK_SECRET)%')] ?string $stripeWebhookSecret, #[Autowire('%env(STRIPE_CURRENCY)%')] string $stripeCurrency): JsonResponse
    {
        $payload = @file_get_contents('php://input');
        $sigHeader = $request->headers->get('HTTP_STRIPE_SIGNATURE');
        $event = null;

        Stripe::setApiKey($stripeSecretKey);

        if ($stripeWebhookSecret) {
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sigHeader, $stripeWebhookSecret
                );
            } catch(\UnexpectedValueException $e) {
                // Invalid payload
                return new JsonResponse(['error' => 'Invalid payload'], 400);
            } catch(\Stripe\Exception\SignatureVerificationException $e) {
                // Invalid signature
                return new JsonResponse(['error' => 'Invalid signature'], 400);
            }
        } else {
            // For testing without webhook signing (less secure in production)
            try {
                $event = \Stripe\Event::constructFrom($payload);
            } catch (\UnexpectedValueException $e) {
                return new JsonResponse(['error' => 'Invalid payload'], 400);
            }
        }


        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $evenementId = $session->metadata->evenementId;
            $userId = $session->metadata->userId;

            $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);
            $user = $entityManager->getRepository(\App\Entity\User::class)->find($userId);
            $participantRepository = $entityManager->getRepository(Participantevenement::class);

            if ($evenement && $user) {
                $existingParticipation = $participantRepository->findOneBy([
                    'user' => $user,
                    'evenement' => $evenement,
                ]);

                if (!$existingParticipation) {
                    $participant = new Participantevenement();
                    $participant->setUser($user);
                    $participant->setEvenement($evenement);
                    $participant->setDateInscription(new \DateTime());
                    $participant->setEtatPaiement('PAYÉ');
                    $entityManager->persist($participant);
                    $entityManager->flush();

                    // Optionally send a confirmation email here
                }
            }
        }

        return new JsonResponse(['status' => 'success'], 200);
    }



//    #[Route('/mes-evenements', name: 'app_mes_evenements')]
//    public function mesEvenements(EntityManagerInterface $entityManager): Response
//    {
//        $user = $this->getUser();
//
//        if (!$user) {
//            $this->addFlash('error', 'Vous devez être connecté pour voir vos événements.');
//            return $this->redirectToRoute('app_login'); // Or another appropriate route
//        }
//
//        $participations = $entityManager->getRepository(Participantevenement::class)->findBy(['user' => $user]);
//
//        return $this->render('evenement/mes_evenements.html.twig', [
//            'participations' => $participations,
//        ]);
//    }


//    #[Route('/mes-evenements', name: 'app_mes_evenements')]
//    public function mesEvenements(EntityManagerInterface $entityManager, Request $request): Response
//    {
//        $user = $this->getUser();
//
//        if (!$user) {
//            $this->addFlash('error', 'Vous devez être connecté pour voir vos événements.');
//            return $this->redirectToRoute('app_login');
//        }
//
//        $etatPaiementFilters = $request->query->all('etat_paiement');
//
//        $qb = $entityManager->createQueryBuilder();
//        $qb->select('p')
//            ->from(Participantevenement::class, 'p')
//            ->where('p.user = :user')
//            ->orderBy('p.date_inscription', 'DESC')
//            ->setParameter('user', $user);
//
//        if (!empty($etatPaiementFilters)) {
//            $qb->andWhere('p.etat_paiement IN (:etatPaiement)')
//                ->setParameter('etatPaiement', $etatPaiementFilters);
//        }
//
//        $participations = $qb->getQuery()->getResult();
//
//        return $this->render('evenement/mes_evenements.html.twig', [
//            'participations' => $participations,
//        ]);
//    }

    #[Route('/mes-evenements', name: 'app_mes_evenements')]
    public function mesEvenements(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos événements.');
            return $this->redirectToRoute('app_login');
        }

        $etatPaiementFilters = $request->query->all('etat_paiement');

        $qb = $entityManager->createQueryBuilder();
        $qb->select('p', 'e') // Select both the participation and the event
        ->from(Participantevenement::class, 'p')
            ->leftJoin('p.evenement', 'e') // Join with the Evenement entity
            ->where('p.user = :user')
            ->orderBy('p.date_inscription', 'DESC')
            ->setParameter('user', $user);

        if (!empty($etatPaiementFilters)) {
            $qb->andWhere('p.etat_paiement IN (:etatPaiement)')
                ->setParameter('etatPaiement', $etatPaiementFilters);
        }

        $participations = $qb->getQuery()->getResult();

        // Convert images to base64 for display
//        foreach ($participations as $participation) {
//            $evenement = $participation->getEvenement();
//            if ($evenement && $evenement->getImage()) {
//                $evenement->setBase64Image(base64_encode($evenement->getImage()));
//            }
//        }
        // In your controller, add debug output
        foreach ($participations as $participation) {
            $evenement = $participation->getEvenement();
            if ($evenement) {
                $image = $evenement->getImage();
                if ($image) {
                    // Debug output - check if image exists and its size
                    dump([
                        'event_id' => $evenement->getId(),
                        'image_size' => strlen($image),
                        'first_bytes' => substr($image, 0, 50)
                    ]);

                    $evenement->setBase64Image(base64_encode($image));
                }
            }
        }

        return $this->render('evenement/mes_evenements.html.twig', [
            'participations' => $participations,
        ]);
    }
}

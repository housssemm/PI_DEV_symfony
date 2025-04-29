<?php

namespace App\Controller;

use App\Entity\Evenement;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Participantevenement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class ParticipantEvenementController extends AbstractController{
//    #[Route('/participant/evenement', name: 'app_participant_evenement')]
//    public function index(): Response
//    {
//        return $this->render('participant_evenement/index.html.twig', [
//            'controller_name' => 'ParticipantEvenementController',
//        ]);
//    }
//    #[Route('/evenement/participer/{evenementId}', name: 'app_participer_evenement')]
//    public function participerEvenement(int $evenementId, EntityManagerInterface $entityManager): Response
//    {
//        $user = $this->getUser();
//        if (!$user) {
//            throw $this->createAccessDeniedException('Vous devez être connecté pour participer.');
//        }
//
//        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);
//        if (!$evenement) {
//            throw $this->createNotFoundException("Événement non trouvé.");
//        }
//
//        $existingParticipation = $entityManager->getRepository(ParticipantEvenement::class)->findOneBy([
//            'user' => $user,
//            'evenement' => $evenement,
//        ]);
//
//        if ($existingParticipation) {
//            $this->addFlash('warning', 'Déjà inscrit.');
//            return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
//        }
//
//        $participant = new Participantevenement();
//        $participant->setUser($user);
//        $participant->setEvenement($evenement);
//        $participant->setDateInscription(new \DateTime());
//
//        $entityManager->persist($participant);
//        $entityManager->flush();
//
//        $this->addFlash('success', 'Inscription réussie !');
//        return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
//    }




    #[Route('/evenement/participer/{evenementId}', name: 'app_participer_evenement')]
    public function participerEvenement(
        int $evenementId,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = $this->getUser();
        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);

        if (!$evenement) {
            $this->addFlash('error', 'Événement non trouvé.');
            return $this->redirectToRoute('app_events');
        }

        $existingParticipation = $entityManager->getRepository(ParticipantEvenement::class)->findOneBy([
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

        // ➤ Envoi d'e-mail
        $email = (new Email())
            ->from('no-reply@coachini.com')
            ->to($user->getEmail())
            ->subject('Confirmation d\'inscription à l\'événement')
            ->html("
            <h2>Bonjour {$user->getNom()},</h2>
            <p>Vous êtes bien inscrit à l'événement <strong>{$evenement->getNom()}</strong>.</p>
            <p>Date : {$evenement->getDate()->format('d/m/Y')}</p>
            <p>Statut de paiement : EN ATTENTE</p>
            <br><p>Merci pour votre confiance !</p>
        ");

        $mailer->send($email);

        $this->addFlash('success', 'Inscription réussie et e-mail de confirmation envoyé !');

        return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
    }

}

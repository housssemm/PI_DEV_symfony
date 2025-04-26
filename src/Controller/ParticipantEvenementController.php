<?php

namespace App\Controller;

use App\Entity\Evenement;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Participantevenement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer; // Add this use statement

final class ParticipantEvenementController extends AbstractController{

//
//    #[Route('/evenement/participer/{evenementId}', name: 'app_participer_evenement')]
//    public function participerEvenement(
//        int $evenementId,
//        EntityManagerInterface $entityManager,
//        MessageBusInterface $bus
//    ): Response {
//        $user = $this->getUser();
//        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);
//
//        if (!$evenement) {
//            $this->addFlash('error', 'Événement non trouvé.');
//            return $this->redirectToRoute('app_events');
//        }
//
//        $existingParticipation = $entityManager->getRepository(ParticipantEvenement::class)->findOneBy([
//            'user' => $user,
//            'evenement' => $evenement,
//        ]);
//
//        if ($existingParticipation) {
//            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
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
//        // ➤ Envoi d'e-mail via Messenger
//        $email = (new Email())
//            ->from('houssemm.labidi@gmail.com')
//            ->to($user->getEmail())
//            ->subject('Confirmation d\'inscription à l\'événement')
//            ->html("
//            <h2>Bonjour {$user->getNom()},</h2>
//            <p>Vous êtes bien inscrit à l'événement <strong>{$evenement->getTitre()}</strong>.</p>
//            <p>Date Debut: {$evenement->getDateDebut()->format('d/m/Y')}</p>
//            <p>Statut de paiement : EN ATTENTE</p>
//            <br><p>Merci pour votre confiance !</p>
//        ");
//
//        $bus->dispatch(new SendEmailMessage($email)); // Dispatch the SendEmailMessage
//
//        $this->addFlash('success', 'Inscription réussie et e-mail de confirmation sera envoyé !');
//
//
//        return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
//    }
//
//    #[Route('/evenement/participer/{evenementId}', name: 'app_participer_evenement')]
//    public function participerEvenement(
//        int $evenementId,
//        EntityManagerInterface $entityManager,
//        MailerInterface $mailer
//    ): Response {
//        $user = $this->getUser();
//        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);
//
//        if (!$evenement) {
//            $this->addFlash('error', 'Événement non trouvé.');
//            return $this->redirectToRoute('app_events');
//        }
//
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
//        $participant = new Participantevenement();
//        $participant->setUser($user);
//        $participant->setEvenement($evenement);
//        $participant->setDateInscription(new \DateTime());
//
//        $entityManager->persist($participant);
//        $entityManager->flush();
//
//        // ➤ Envoi de l'e-mail via Mailjet
//        $email = (new Email())
//            ->from('houssem.labidi9393@gmail.com')
//            ->to($user->getEmail())
//            ->subject('Confirmation d\'inscription à l\'événement')
//            ->html("
//            <h2>Bonjour {$user->getNom()},</h2>
//            <p>Vous êtes bien inscrit à l'événement <strong>{$evenement->getTitre()}</strong>.</p>
//            <p>Date Début: {$evenement->getDateDebut()->format('d/m/Y')}</p>
//            <p>Statut de paiement : EN ATTENTE</p>
//            <br><p>Merci pour votre confiance !</p>
//        ");
//
//        $mailer->send($email);
//
//        $this->addFlash('success', 'Inscription réussie et e-mail de confirmation envoyé avec Mailjet !');
//
//        return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
//    }





    #[Route('/evenement/participer/{evenementId}', name: 'app_participer_evenement')]
    public function participerEvenement(
        int $evenementId,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer // Use the injected mailer
    ): Response {
        $user = $this->getUser();
        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);

        if (!$evenement) {
            $this->addFlash('error', 'Événement non trouvé.');
            return $this->redirectToRoute('app_events');
        }

        // Check existing participation
        $existingParticipation = $entityManager->getRepository(Participantevenement::class)->findOneBy([
            'user' => $user,
            'evenement' => $evenement,
        ]);

        if ($existingParticipation) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
            return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
        }

        // Register participation
        $participant = new Participantevenement();
        $participant->setUser($user);
        $participant->setEvenement($evenement);
        $participant->setDateInscription(new \DateTime());

        $entityManager->persist($participant);
        $entityManager->flush();

        // Send email
        $email = (new Email())
            ->from('houssem.labidi9393@gmail.com')
            ->to($user->getEmail())
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
            $this->addFlash('success', 'Inscription réussie et e-mail de confirmation envoyé ! '.$user->getEmail());
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'e-mail : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_event_details', ['id' => $evenementId]);
    }
}

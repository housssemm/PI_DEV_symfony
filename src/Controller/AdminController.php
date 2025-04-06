<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    #[Route('/admin/requests', name: 'admin_requests')]
    public function showRequests(EntityManagerInterface $em): Response
    {
        // Récupérer tous les utilisateurs depuis la base de données
        $userRepository = $em->getRepository(User::class);
        $users = $userRepository->findAll(); // Récupère tous les utilisateurs (y compris les sous-types)

        // Compter les utilisateurs par type (sous-classe)
        $adherentCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\Adherent', 'u')
            ->getQuery()
            ->getSingleScalarResult();

        $coachCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\Coach', 'u')
            ->getQuery()
            ->getSingleScalarResult();

        $createurCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\CreateurEvenement', 'u')
            ->getQuery()
            ->getSingleScalarResult();

        $investisseurCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\InvestisseurProduit', 'u')
            ->getQuery()
            ->getSingleScalarResult();

        // Rendre la template avec les données réelles
        return $this->render('admin/dashboard.html.twig', [
            'users' => $users, // Liste des utilisateurs
            'adherentCount' => $adherentCount,
            'coachCount' => $coachCount,
            'createurCount' => $createurCount,
            'investisseurCount' => $investisseurCount,
        ]);
    }

    #[Route('/admin/validate/{id}', name: 'admin_validate_user', methods: ['POST'])]
    public function validateUser(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if ($user) {
            // Logique pour valider l'utilisateur (à définir selon tes besoins)
            $this->addFlash('success', 'Utilisateur validé avec succès.');
        } else {
            $this->addFlash('error', 'Utilisateur non trouvé.');
        }
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/reject/{id}', name: 'admin_reject_user', methods: ['POST'])]
    public function rejectUser(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if ($user) {
            // Logique pour rejeter l'utilisateur (à définir selon tes besoins)
            $this->addFlash('success', 'Utilisateur rejeté avec succès.');
        } else {
            $this->addFlash('error', 'Utilisateur non trouvé.');
        }
        return $this->redirectToRoute('admin_dashboard');
    }
}
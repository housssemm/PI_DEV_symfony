<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Form\CoachType;
use App\Repository\CoachRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CoachController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CoachRepository $coachRepository;
    private UserController $userController;

    public function __construct(
        EntityManagerInterface $entityManager,
        CoachRepository $coachRepository,
        UserController $userController
    ) {
        $this->entityManager = $entityManager;
        $this->coachRepository = $coachRepository;
        $this->userController = $userController;
    }

    #[Route('/coach/register', name: 'app_coach_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $coach = new Coach();
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajout de l'image par défaut si aucune n'est fournie (comme dans UserController)
            $defaultImage = '/img/OIP.jpeg';
            $imageToInsert = $coach->getImage() ?: $defaultImage;
            $coach->setImage($imageToInsert);

            // Appel à UserController pour créer la partie User
            $userId = $this->userController->createAndReturnId($coach);
            if ($userId === null) { // Vérifie si l'ID est valide
                throw new \Exception('Erreur lors de la création de l\'utilisateur.');
            }

            // Persister les données spécifiques de Coach
            $this->entityManager->persist($coach);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_login'); // Redirection vers login comme pour User
        }

        return $this->render('coach/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // Méthodes CRUD brutes
    public function createCoach(Coach $coach): int
    {
        $defaultImage = '/img/OIP.jpeg';
        $imageToInsert = $coach->getImage() ?: $defaultImage;
        $coach->setImage($imageToInsert);

        return $this->userController->createAndReturnId($coach);
    }

    public function getAll(): array
    {
        return $this->coachRepository->findAll();
    }

    public function updateCoach(Coach $coach): void
    {
        $this->userController->updateUser($coach);
        $this->entityManager->flush();
    }

    public function deleteCoach(Coach $coach): void
    {
        $this->userController->deleteUser($coach);
        $this->entityManager->flush();
    }
} 
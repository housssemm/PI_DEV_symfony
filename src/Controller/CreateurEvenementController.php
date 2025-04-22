<?php

namespace App\Controller;

use App\Entity\CreateurEvenement;
use App\Form\CreateurEvenementType;
use App\Repository\CreateurevenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateurEvenementController extends AbstractController
{
    private CreateurevenementRepository $createurEvenementRepository;
    private EntityManagerInterface $entityManager;
    private UserController $userController;

    public function __construct(
        CreateurevenementRepository $createurEvenementRepository,
        EntityManagerInterface $entityManager,
        UserController $userController
    ) {
        $this->createurEvenementRepository = $createurEvenementRepository;
        $this->entityManager = $entityManager;
        $this->userController = $userController;
    }

    #[Route('/createur/register', name: 'app_createur_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $createur = new CreateurEvenement();
        $form = $this->createForm(CreateurEvenementType::class, $createur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajout de l'image par défaut si aucune n'est fournie
            $defaultImage = '/img/OIP.jpeg';
            $imageToInsert = $createur->getImage() ?: $defaultImage;
            $createur->setImage($imageToInsert);

            // Appel à UserController pour créer la partie User
            $userId = $this->userController->createAndReturnId($createur);
            if ($userId === null) {
                throw new \Exception('Erreur lors de la création de l\'utilisateur.');
            }

            // Persister les données spécifiques de CreateurEvenement
            $this->entityManager->persist($createur);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('createur/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // Méthodes CRUD brutes
    public function createCreateurEvenement(CreateurEvenement $createur): int
    {
        $defaultImage = '/img/OIP.jpeg';
        $imageToInsert = $createur->getImage() ?: $defaultImage;
        $createur->setImage($imageToInsert);

        return $this->userController->createAndReturnId($createur);
    }

    public function getAll(): array
    {
        return $this->createurEvenementRepository->findAll();
    }

    public function updateCreateurEvenement(CreateurEvenement $createur): void
    {
        $this->userController->updateUser($createur);
        $this->entityManager->flush();
    }

    public function deleteCreateurEvenement(CreateurEvenement $createur): void
    {
        $this->userController->deleteUser($createur);
        $this->entityManager->flush();
    }
} 
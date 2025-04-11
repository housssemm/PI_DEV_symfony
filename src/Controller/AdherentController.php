<?php

namespace App\Controller;

use App\Entity\Adherent;
use App\Form\AdherentType;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdherentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AdherentRepository $adherentRepository;
    private UserController $userController;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdherentRepository $adherentRepository,
        UserController $userController
    ) {
        $this->entityManager = $entityManager;
        $this->adherentRepository = $adherentRepository;
        $this->userController = $userController;
    }

    #[Route('/adherent/register', name: 'app_adherent_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $adherent = new Adherent();
        $form = $this->createForm(AdherentType::class, $adherent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajout de l'image par défaut si aucune n'est fournie
            $defaultImage = '/img/OIP.jpeg';
            $imageToInsert = $adherent->getImage() ?: $defaultImage;
            $adherent->setImage($imageToInsert);

            // Appel à UserController pour créer la partie User
            $userId = $this->userController->createAndReturnId($adherent);
            if ($userId === null) {
                throw new \Exception('Erreur lors de la création de l\'utilisateur.');
            }

            // Persister les données spécifiques de Adherent
            $this->entityManager->persist($adherent);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('adherent/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // Méthodes CRUD brutes
    public function createAdherent(Adherent $adherent): int
    {
        $defaultImage = '/img/OIP.jpeg';
        $imageToInsert = $adherent->getImage() ?: $defaultImage;
        $adherent->setImage($imageToInsert);

        return $this->userController->createAndReturnId($adherent);
    }

    public function getAll(): array
    {
        return $this->adherentRepository->findAll();
    }

    public function updateAdherent(Adherent $adherent): void
    {
        $this->userController->updateUser($adherent);
        $this->entityManager->flush();
    }

    public function deleteAdherent(Adherent $adherent): void
    {
        $this->userController->deleteUser($adherent);
        $this->entityManager->flush();
    }
} 
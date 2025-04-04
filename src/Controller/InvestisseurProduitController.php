<?php

namespace App\Controller;

use App\Entity\InvestisseurProduit;
use App\Form\InvestisseurProduitType;
use App\Repository\InvestisseurProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvestisseurProduitController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private InvestisseurProduitRepository $investisseurProduitRepository;
    private UserController $userController;

    public function __construct(
        EntityManagerInterface $entityManager,
        InvestisseurProduitRepository $investisseurProduitRepository,
        UserController $userController
    ) {
        $this->entityManager = $entityManager;
        $this->investisseurProduitRepository = $investisseurProduitRepository;
        $this->userController = $userController;
    }

    #[Route('/investisseur/register', name: 'app_investisseur_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $investisseur = new InvestisseurProduit();
        $form = $this->createForm(InvestisseurProduitType::class, $investisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajout de l'image par défaut si aucune n'est fournie
            $defaultImage = '/img/OIP.jpeg';
            $imageToInsert = $investisseur->getImage() ?: $defaultImage;
            $investisseur->setImage($imageToInsert);

            // Appel à UserController pour créer la partie User
            $userId = $this->userController->createAndReturnId($investisseur);
            if ($userId === null) {
                throw new \Exception('Erreur lors de la création de l\'utilisateur.');
            }

            // Persister les données spécifiques de InvestisseurProduit
            $this->entityManager->persist($investisseur);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('investisseur/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // Méthodes CRUD brutes
    public function createInvestisseurProduit(InvestisseurProduit $investisseur): int
    {
        $defaultImage = '/img/OIP.jpeg';
        $imageToInsert = $investisseur->getImage() ?: $defaultImage;
        $investisseur->setImage($imageToInsert);

        return $this->userController->createAndReturnId($investisseur);
    }

    public function getAll(): array
    {
        return $this->investisseurProduitRepository->findAll();
    }

    public function updateInvestisseurProduit(InvestisseurProduit $investisseur): void
    {
        $this->userController->updateUser($investisseur);
        $this->entityManager->flush();
    }

    public function deleteInvestisseurProduit(InvestisseurProduit $investisseur): void
    {
        $this->userController->deleteUser($investisseur);
        $this->entityManager->flush();
    }
} 
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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CoachController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CoachRepository $coachRepository;
    private UserController $userController;
    private SluggerInterface $slugger;

    public function __construct(
        EntityManagerInterface $entityManager,
        CoachRepository $coachRepository,
        UserController $userController,
        SluggerInterface $slugger
    ) {
        $this->entityManager = $entityManager;
        $this->coachRepository = $coachRepository;
        $this->userController = $userController;
        $this->slugger = $slugger;
    }

    #[Route('/coach/register', name: 'app_coach_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $coach = new Coach();
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'image
            $defaultImage = '/img/OIP.jpeg';
            $imageToInsert = $coach->getImage() ?: $defaultImage;
            $coach->setImage($imageToInsert);

            // Gérer le fichier CV
            $cvFile = $form->get('cv')->getData();
            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newCvFilename = $safeFilename.'-'.uniqid().'.'.$cvFile->guessExtension();
                try {
                    $cvFile->move(
                        $this->getParameter('cv_directory'),
                        $newCvFilename
                    );
                    $coach->setCv($newCvFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload du CV : ' . $e->getMessage());
                    return $this->render('coach/register.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }
            }

            // Appel à UserController pour créer la partie User
            $userId = $this->userController->createAndReturnId($coach);
            if ($userId === null) {
                throw new \Exception('Erreur lors de la création de l\'utilisateur.');
            }

            $this->entityManager->persist($coach);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('coach/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // Méthodes CRUD brutes (inchangées)
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
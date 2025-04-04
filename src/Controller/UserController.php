<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $this->logger->info('Requête reçue pour /register', [
            'method' => $request->getMethod(),
            'data' => $request->request->all(),
            'files' => $request->files->all()
        ]);

        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('plainPassword');
            $imageFile = $request->files->get('image');
            $agreeTerms = $request->request->get('agreeTerms');
            $userType = $request->request->get('userType');

            $this->logger->info('Données reçues', [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'plainPassword' => $plainPassword ? '[provided]' : null,
                'image' => $imageFile ? $imageFile->getClientOriginalName() : null,
                'agreeTerms' => $agreeTerms,
                'userType' => $userType
            ]);

            // Données des sous-formulaires (null si non présentes)
            $adherentData = $postData['adherent'] ?? null;
            $coachData = $postData['coach'] ?? null;
            $createurData = $postData['createur'] ?? null;
            $investisseurData = $postData['investisseur'] ?? null;
            $this->logger->info('Sous-formulaires', [
                'adherent' => $adherentData,
                'coach' => $coachData,
                'createur' => $createurData,
                'investisseur' => $investisseurData
            ]);

            if (!$nom || !$prenom || !$email || !$plainPassword || !$agreeTerms) {
                $this->logger->warning('Champs manquants ou conditions non acceptées');
                $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis et les conditions acceptées.');
                return $this->render('registration/register.html.twig');
            }

            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setUserType($userType ?: 'adherent');

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    $user->setImage('/uploads/' . $newFilename);
                    $this->logger->info('Image uploadée', ['filename' => $newFilename]);
                } catch (FileException $e) {
                    $this->logger->error('Erreur upload image', ['exception' => $e->getMessage()]);
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                    return $this->render('registration/register.html.twig');
                }
            } else {
                $user->setImage('/img/OIP.jpeg');
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->logger->info('Utilisateur créé', ['email' => $user->getEmail()]);

            $this->addFlash('success', 'Compte créé avec succès ! Vous allez être redirigé vers la connexion.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig');
    }



    public function createAndReturnId(User $user): int
    {
        $plainPassword = $user->getPlainPassword();
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->eraseCredentials();
        }
        if (!$user->getUserType()) {
            $user->setUserType('adherent');
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user->getId();
    }

    public function getAll(): array
    {
        return $this->userRepository->findAll();
    }

    public function updateUser(User $user): void
    {
        $this->entityManager->flush();
    }

    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use App\Entity\Adherent;
use App\Entity\Coach;
use App\Entity\CreateurEvenement;
use App\Entity\InvestisseurProduit;

class ProfileController extends AbstractController
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'edit_mode' => true
        ]);
    }

    #[Route('/profile/update-password', name: 'app_profile_update_password')]
    public function updatePassword(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'password_mode' => true
        ]);
    }

    #[Route('/profile/update-image', name: 'app_profile_update_image', methods: ['POST'])]
    public function updateProfileImage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $uploadedFile = $request->files->get('profileImage');

        if (!$uploadedFile) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun fichier n\'a été uploadé']);
        }

        // Vérification du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
            return new JsonResponse(['success' => false, 'message' => 'Type de fichier non autorisé']);
        }

        // Génération d'un nom de fichier unique
        $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();

        try {
            // Déplacement du fichier vers le dossier de destination
            $uploadedFile->move(
                $this->getParameter('user_images_directory'),
                $newFilename
            );

            // Mise à jour de l'utilisateur
            $user->setImage($newFilename);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'filename' => $newFilename
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de l\'upload']);
        }
    }

    #[Route('/profile/update-info', name: 'app_profile_update_info', methods: ['POST'])]
    public function updateInfo(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }

        try {
            // Mettre à jour les informations de base
            $user->setPrenom($request->request->get('prenom', $user->getPrenom()));
            $user->setNom($request->request->get('nom', $user->getNom()));
            $user->setEmail($request->request->get('email', $user->getEmail()));

            // Mettre à jour les informations spécifiques selon le type d'utilisateur
            if ($user instanceof Adherent) {
                $user->setAge((int)$request->request->get('age', $user->getAge()));
                $user->setGenre($request->request->get('genre', $user->getGenre()));
                $user->setTaille((float)$request->request->get('taille', $user->getTaille()));
                $user->setPoids((float)$request->request->get('poids', $user->getPoids()));
            } elseif ($user instanceof Coach) {
                $user->setSpecialite($request->request->get('specialite', $user->getSpecialite()));
                $user->setAnneeExperience((int)$request->request->get('anneeExperience', $user->getAnneeExperience()));
            } elseif ($user instanceof CreateurEvenement) {
                $user->setNomOrganisation($request->request->get('nomOrganisation', $user->getNomOrganisation()));
                $user->setDescriptionCreateur($request->request->get('descriptionCreateur', $user->getDescriptionCreateur()));
                $user->setAdresseCreateur($request->request->get('adresseCreateur', $user->getAdresseCreateur()));
                $user->setTelephoneCreateur($request->request->get('telephoneCreateur', $user->getTelephoneCreateur()));
            } elseif ($user instanceof InvestisseurProduit) {
                $user->setNomEntreprise($request->request->get('nomEntreprise', $user->getNomEntreprise()));
                $user->setDescriptionInvestisseur($request->request->get('descriptionInvestisseur', $user->getDescriptionInvestisseur()));
                $user->setAdresseInvestisseur($request->request->get('adresseInvestisseur', $user->getAdresseInvestisseur()));
                $user->setTelephoneInvestisseur($request->request->get('telephoneInvestisseur', $user->getTelephoneInvestisseur()));
            }

            $this->entityManager->flush();

            return new JsonResponse(['success' => true, 'message' => 'Informations mises à jour avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
        }
    }

    #[Route('/profile/update-cv', name: 'app_profile_update_cv', methods: ['POST'])]
    public function updateCV(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['success' => false, 'message' => 'Utilisateur non connecté'], JsonResponse::HTTP_UNAUTHORIZED);
            }

            if (!($user instanceof Coach)) {
                return new JsonResponse([
                    'success' => false, 
                    'message' => 'Cette fonctionnalité est réservée aux coachs',
                    'user_type' => get_class($user)
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            $cvFile = $request->files->get('cv');
            if (!$cvFile) {
                return new JsonResponse([
                    'success' => false, 
                    'message' => 'Aucun fichier n\'a été téléchargé',
                    'files' => $request->files->all()
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Vérification du type de fichier
            $allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];

            $mimeType = $cvFile->getMimeType();
            $extension = $cvFile->guessExtension();
            
            if (!in_array($mimeType, $allowedTypes)) {
                return new JsonResponse([
                    'success' => false, 
                    'message' => 'Format de fichier non supporté.',
                    'details' => [
                        'mime_type' => $mimeType,
                        'extension' => $extension,
                        'original_name' => $cvFile->getClientOriginalName(),
                        'allowed_types' => $allowedTypes
                    ]
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Vérification de la taille du fichier (max 5MB)
            if ($cvFile->getSize() > 5 * 1024 * 1024) {
                return new JsonResponse([
                    'success' => false, 
                    'message' => 'Le fichier est trop volumineux. Taille maximale autorisée : 5MB',
                    'size' => $cvFile->getSize()
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Génération d'un nom de fichier unique
            $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;

            // Vérification que le répertoire existe
            $cvDirectory = $this->getParameter('cv_directory');
            if (!file_exists($cvDirectory)) {
                if (!mkdir($cvDirectory, 0777, true)) {
                    return new JsonResponse([
                        'success' => false, 
                        'message' => 'Impossible de créer le répertoire de destination',
                        'details' => [
                            'directory' => $cvDirectory,
                            'exists' => file_exists($cvDirectory),
                            'is_dir' => is_dir($cvDirectory),
                            'permissions' => substr(sprintf('%o', fileperms($cvDirectory)), -4)
                        ]
                    ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            // Vérification des permissions du répertoire
            if (!is_writable($cvDirectory)) {
                chmod($cvDirectory, 0777);
                if (!is_writable($cvDirectory)) {
                    return new JsonResponse([
                        'success' => false, 
                        'message' => 'Le répertoire de destination n\'a pas les permissions d\'écriture',
                        'details' => [
                            'directory' => $cvDirectory,
                            'permissions' => substr(sprintf('%o', fileperms($cvDirectory)), -4),
                            'owner' => fileowner($cvDirectory),
                            'group' => filegroup($cvDirectory)
                        ]
                    ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            // Déplacement du fichier
            try {
                $cvFile->move($cvDirectory, $newFilename);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Erreur lors du déplacement du fichier',
                    'details' => [
                        'error' => $e->getMessage(),
                        'directory' => $cvDirectory,
                        'filename' => $newFilename,
                        'writable' => is_writable($cvDirectory),
                        'exists' => file_exists($cvDirectory),
                        'permissions' => substr(sprintf('%o', fileperms($cvDirectory)), -4)
                    ]
                ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Vérification que le fichier a bien été déplacé
            $finalPath = $cvDirectory . DIRECTORY_SEPARATOR . $newFilename;
            if (!file_exists($finalPath)) {
                return new JsonResponse([
                    'success' => false, 
                    'message' => 'Le fichier n\'a pas pu être déplacé',
                    'details' => [
                        'path' => $finalPath,
                        'directory_exists' => file_exists($cvDirectory),
                        'directory_writable' => is_writable($cvDirectory)
                    ]
                ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Suppression de l'ancien CV s'il existe
            $oldCv = $user->getCv();
            if ($oldCv && file_exists($cvDirectory . DIRECTORY_SEPARATOR . $oldCv)) {
                try {
                    unlink($cvDirectory . DIRECTORY_SEPARATOR . $oldCv);
                } catch (\Exception $e) {
                    // Log l'erreur mais continue
                    error_log('Erreur lors de la suppression de l\'ancien CV: ' . $e->getMessage());
                }
            }

            // Mise à jour de l'entité
            try {
                $user->setCv($newFilename);
                $entityManager->flush();
            } catch (\Exception $e) {
                // Si l'enregistrement en base échoue, on supprime le fichier uploadé
                if (file_exists($finalPath)) {
                    unlink($finalPath);
                }
                throw $e;
            }

            return new JsonResponse([
                'success' => true, 
                'message' => 'CV mis à jour avec succès',
                'filename' => $newFilename
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'details' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 
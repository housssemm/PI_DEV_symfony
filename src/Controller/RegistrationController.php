<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Adherent;
use App\Entity\Coach;
use App\Entity\CreateurEvenement;
use App\Entity\InvestisseurProduit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        if ($request->isMethod('POST')) {
            // Récupérer les données manuellement
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('plainPassword');
            $userType = $request->request->get('selectedUserType');

            // Avant de récupérer tous les fichiers, on supprime la clé "coach" du bag des fichiers
            // si elle existe pour éviter la fusion avec les données POST.
            if ($request->files->has('coach')) {
                $request->files->remove('coach');
            }
            $allFiles = $request->files->all();
            // dump($allFiles); // Pour débogage

            // Validation basique
            if (!$email || !$plainPassword || !$userType) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
                return $this->render('registration/register.html.twig');
            }

            // Gérer l'upload de l'image
            $newImageFilename = null;
            if (!empty($allFiles['image'])) {
                $imageFile = $allFiles['image'];
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newImageFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('user_images_directory'),
                        $newImageFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image : ' . $e->getMessage());
                    return $this->render('registration/register.html.twig');
                }
            }

            // Créer l'entité spécifique selon le type d'utilisateur
            switch ($userType) {
                case 'adherent':
                    $user = new Adherent();
                    $adherentData = $request->request->get('adherent') ?? [];
                    $user->setPoids($adherentData['poids'] ?? null);
                    $user->setTaille($adherentData['taille'] ?? null);
                    $user->setAge($adherentData['age'] ?? null);
                    $user->setGenre($adherentData['genre'] ?? null);
                    $user->setObjectifPersonnel($adherentData['objectif'] ?? null);
                    $user->setNiveauActivite(null);
                    $user->setRoles(['ROLE_ADHERENT']);
                    break;
                case 'coach':
                    $user = new Coach();
                    $coachData = $request->request->get('coach') ?? [];
                    $user->setAnneeExperience($coachData['experience'] ?? null);
                    $user->setSpecialite($coachData['specialite'] ?? null);
                    $newCvFilename = null;
                    // Récupération du fichier CV depuis le champ "coach_cv"
                    $cvFile = $request->files->get('coach_cv');
                    if ($cvFile) {
                        $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newCvFilename = $safeFilename.'-'.uniqid().'.'.$cvFile->guessExtension();
                        try {
                            $cvFile->move(
                                $this->getParameter('cv_directory'),
                                $newCvFilename
                            );
                            $user->setCv($newCvFilename);
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors de l’upload du CV : ' . $e->getMessage());
                            return $this->render('registration/register.html.twig');
                        }
                    }
                    $user->setRoles(['ROLE_COACH']);
                    break;
                case 'createur':
                    $user = new CreateurEvenement();
                    $createurData = $request->request->get('createur') ?? [];
                    $user->setNomOrganisation($createurData['organisation'] ?? null);
                    $user->setDescriptionCreateur($createurData['description'] ?? null);
                    $user->setAdresseCreateur($createurData['adresse'] ?? null);
                    $user->setTelephoneCreateur($createurData['telephone'] ?? null);
                    $user->setRoles(['ROLE_CREATEUR_EVENEMENT']);
                    break;
                case 'investisseur':
                    $user = new InvestisseurProduit();
                    $investisseurData = $request->request->get('investisseur') ?? [];
                    $user->setNomEntreprise($investisseurData['entreprise'] ?? null);
                    $user->setDescriptionInvestisseur($investisseurData['description'] ?? null);
                    $user->setAdresseInvestisseur($investisseurData['adresse'] ?? null);
                    $user->setTelephoneInvestisseur($investisseurData['telephone'] ?? null);
                    $user->setRoles(['ROLE_INVESTISSEUR_PRODUIT']);
                    break;
                default:
                    $this->addFlash('error', 'Type d’utilisateur invalide.');
                    return $this->render('registration/register.html.twig');
            }

            // Définir les propriétés communes
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            if ($newImageFilename) {
                $user->setImage($newImageFilename);
            }

            // Sauvegarder dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig');
    }
}

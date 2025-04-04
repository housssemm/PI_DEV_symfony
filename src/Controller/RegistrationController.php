<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Adherent;
use App\Entity\Coach;
use App\Entity\CreateurEvenement;
use App\Entity\InvestisseurProduit;
use App\Form\RegistrationFormType;
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
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe mal pendant l'upload
                }

                $user->setImage($newFilename);
            }

            // Encode le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Définir le type d'utilisateur
            $userType = $form->get('userType')->getData();
            $user->setUserType($userType);

            // Créer l'entité spécifique selon le type d'utilisateur
            switch ($userType) {
                case 'adherent':
                    $specificUser = new Adherent();
                    $specificUser->setPoids($form->get('poids')->getData());
                    $specificUser->setTaille($form->get('taille')->getData());
                    $specificUser->setAge($form->get('age')->getData());
                    $specificUser->setGenre($form->get('genre')->getData());
                    $specificUser->setObjectifPersonnel($form->get('objectifPersonnel')->getData());
                    $specificUser->setNiveauActivite($form->get('niveauActivite')->getData());
                    $specificUser->setRoles(['ROLE_ADHERENT']);
                    break;
                case 'coach':
                    $specificUser = new Coach();
                    $specificUser->setAnneeExperience($form->get('anneeExperience')->getData());
                    $specificUser->setSpecialite($form->get('specialite')->getData());
                    // Gérer l'upload du CV
                    $cvFile = $form->get('cv')->getData();
                    if ($cvFile) {
                        $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$cvFile->guessExtension();
                        try {
                            $cvFile->move(
                                $this->getParameter('cv_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            // Gérer l'exception
                        }
                        $specificUser->setCv($newFilename);
                    }
                    $specificUser->setRoles(['ROLE_COACH']);
                    break;
                case 'createur_evenement':
                    $specificUser = new CreateurEvenement();
                    $specificUser->setNomOrganisation($form->get('nomOrganisation')->getData());
                    $specificUser->setDescriptionCreateur($form->get('descriptionCreateur')->getData());
                    $specificUser->setAdresseCreateur($form->get('adresseCreateur')->getData());
                    $specificUser->setTelephoneCreateur($form->get('telephoneCreateur')->getData());
                    $specificUser->setRoles(['ROLE_CREATEUR_EVENEMENT']);
                    break;
                case 'investisseur_produit':
                    $specificUser = new InvestisseurProduit();
                    $specificUser->setNomEntreprise($form->get('nomEntreprise')->getData());
                    $specificUser->setDescriptionInvestisseur($form->get('descriptionInvestisseur')->getData());
                    $specificUser->setAdresseInvestisseur($form->get('adresseInvestisseur')->getData());
                    $specificUser->setTelephoneInvestisseur($form->get('telephoneInvestisseur')->getData());
                    $specificUser->setRoles(['ROLE_INVESTISSEUR_PRODUIT']);
                    break;
            }

            // Copier les propriétés communes
            $specificUser->setEmail($user->getEmail());
            $specificUser->setPassword($user->getPassword());
            $specificUser->setNom($user->getNom());
            $specificUser->setPrenom($user->getPrenom());
            $specificUser->setImage($user->getImage());

            $entityManager->persist($specificUser);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
} 
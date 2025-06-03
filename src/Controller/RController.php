<?php
//
//namespace App\Controller;
//
//use App\Entity\Adherent;
//use App\Entity\Coach;
//use App\Entity\CreateurEvenement;
//use App\Entity\InvestisseurProduit;
//use App\Form\R;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
//
//class RController extends AbstractController
//{
//    #[Route('/r', name: 'app_r')]
//    public function register(
//        Request $request,
//        EntityManagerInterface $entityManager,
//        UserPasswordHasherInterface $passwordHasher
//    ): Response {
//        $form = $this->createForm(R::class);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $selectedType = $form->get('type')->getData();
//
//            switch ($selectedType) {
//                case 'adherent':
//                    $user = new Adherent();
//                    $user->setPoids((float) $form->get('poids')->getData());
//                    $user->setTaille((float) $form->get('taille')->getData());
//                    $user->setAge((int) $form->get('age')->getData());
//                    $user->setGenre($form->get('genre')->getData());
//                    $user->setObjectifPersonnel($form->get('objectifPersonnel')->getData());
//                    $user->setNiveauActivite($form->get('niveauActivite')->getData());
//                    break;
//                case 'coach':
//                    $user = new Coach();
//                    $user->setAnneeExperience((int) $form->get('anneeExperience')->getData());
//                    $user->setCertificat_valide(false); // Valeur par défaut
//                    $user->setSpecialite($form->get('specialite')->getData());
//                    $cvFile = $form->get('cv')->getData();
//                    if ($cvFile) {
//                        $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
//                        // Utilisation de iconv et preg_replace comme alternative
//                        $safeFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $originalFilename);
//                        $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '', $safeFilename);
//                        $newFilename = $safeFilename.'-'.uniqid().'.'.$cvFile->guessExtension();
//                        try {
//                            $cvFile->move(
//                                $this->getParameter('cv_directory'),
//                                $newFilename
//                            );
//                        } catch (\Exception $e) {
//                            $this->addFlash('error', 'Erreur lors de l\'upload du fichier CV');
//                            return $this->redirectToRoute('app_r');
//                        }
//                        // On stocke le nom du fichier dans l'entité
//                        $user->setCv($newFilename);
//                    } else {
//                        $this->addFlash('error', 'Le fichier CV est requis');
//                        return $this->redirectToRoute('app_r');
//                    }
//                    break;
//
//                case 'createur_evenement':
//                    $user = new CreateurEvenement();
//                    $user->setNomOrganisation($form->get('nomOrganisation')->getData());
//                    $user->setDescriptionCreateur($form->get('descriptionCreateur')->getData());
//                    $user->setAdresseCreateur($form->get('adresseCreateur')->getData());
//                    $user->setTelephoneCreateur($form->get('telephoneCreateur')->getData());
//                    // Ne récupère plus certificatValide depuis le formulaire.
//                    $user->setCertificatValide(false); // Valeur par défaut
//                    break;
//                case 'investisseur_produit':
//                    $user = new InvestisseurProduit();
//                    $user->setNomEntreprise($form->get('nomEntreprise')->getData());
//                    $user->setDescriptionInvestisseur($form->get('descriptionInvestisseur')->getData());
//                    $user->setAdresseInvestisseur($form->get('adresseInvestisseur')->getData());
//                    $user->setTelephoneInvestisseur($form->get('telephoneInvestisseur')->getData());
//                    // Ne récupère plus certificatValide depuis le formulaire.
//                    $user->setCertificatValide(false); // Valeur par défaut
//                    break;
//                default:
//                    $this->addFlash('error', 'Type d\'utilisateur inconnu');
//                    return $this->redirectToRoute('app_r');
//            }
//
//            // Champs communs
//            $user->setEmail($form->get('email')->getData());
//            $user->setNom($form->get('nom')->getData());
//            $user->setPrenom($form->get('prenom')->getData());
//
//            $plainPassword = $form->get('plainPassword')->getData();
//            $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
//            $user->setPassword($encodedPassword);
//
//            $user->setRoles(['ROLE_USER']);
//
//            $entityManager->persist($user);
//            $entityManager->flush();
//
//            $this->addFlash('success', 'Inscription réussie !');
//            return $this->redirectToRoute('app_home');
//        }
//
//        return $this->render('r/index.html.twig', [
//            'rForm' => $form->createView(),
//        ]);
//    }
//}


namespace App\Controller;

use App\Entity\Adherent;
use App\Entity\Coach;
use App\Entity\CreateurEvenement;
use App\Entity\InvestisseurProduit;
use App\Form\RType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RController extends AbstractController
{
    #[Route('/r', name: 'app_r')]
    public function register(
        Request                     $request,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $form = $this->createForm(RType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du type d'utilisateur depuis le champ caché "selectedUserType"
            $selectedType = $request->request->get('selectedUserType');

            switch ($selectedType) {
                case 'adherent':
                    $user = new Adherent();
                    $user->setPoids((float)$form->get('poids')->getData());
                    $user->setTaille((float)$form->get('taille')->getData());
                    $user->setAge((int)$form->get('age')->getData());
                    $user->setGenre($form->get('genre')->getData());
                    $user->setObjectifPersonnel($form->get('objectifPersonnel')->getData());
                    $user->setNiveauActivite($form->get('niveauActivite')->getData());
                    break;
                case 'coach':
                    $user = new Coach();
                    $user->setAnneeExperience((int)$form->get('anneeExperience')->getData());
                    $user->setCertificat_valide(false); // Valeur par défaut
                    $user->setSpecialite($form->get('specialite')->getData());
                    $cvFile = $form->get('cv')->getData();
                    if ($cvFile) {
                        $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $originalFilename);
                        $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '', $safeFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $cvFile->guessExtension();
                        try {
                            $cvFile->move(
                                $this->getParameter('cv_directory'),
                                $newFilename
                            );
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Erreur lors de l\'upload du fichier CV');
                            return $this->redirectToRoute('app_r');
                        }
                        $user->setCv($newFilename);
                    } else {
                        $this->addFlash('error', 'Le fichier CV est requis');
                        return $this->redirectToRoute('app_r');
                    }
                    break;
                case 'createur':
                    $user = new CreateurEvenement();
                    $user->setNomOrganisation($form->get('nomOrganisation')->getData());
                    $user->setDescriptionCreateur($form->get('descriptionCreateur')->getData());
                    $user->setAdresseCreateur($form->get('adresseCreateur')->getData());
                    $user->setTelephoneCreateur($form->get('telephoneCreateur')->getData());
                    $user->setCertificatValide(false); // Valeur par défaut
                    break;
                case 'investisseur':
                    $user = new InvestisseurProduit();
                    $user->setNomEntreprise($form->get('nomEntreprise')->getData());
                    $user->setDescriptionInvestisseur($form->get('descriptionInvestisseur')->getData());
                    $user->setAdresseInvestisseur($form->get('adresseInvestisseur')->getData());
                    $user->setTelephoneInvestisseur($form->get('telephoneInvestisseur')->getData());
                    $user->setCertificatValide(false); // Valeur par défaut
                    break;
                default:
                    $this->addFlash('error', 'Type d\'utilisateur inconnu');
                    return $this->redirectToRoute('app_r');
            }

            // Champs communs
            $user->setEmail($form->get('email')->getData());
            $user->setNom($form->get('nom')->getData());
            $user->setPrenom($form->get('prenom')->getData());

            $plainPassword = $form->get('plainPassword')->getData();
            $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);

            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('r/index.html.twig', [
            'rForm' => $form->createView(),
        ]);
    }
}

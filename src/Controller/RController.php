<?php
namespace App\Controller;

use App\Entity\Adherent;
use App\Entity\Coach;
use App\Entity\CreateurEvenement;
use App\Entity\InvestisseurProduit;
use App\Form\R;
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
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(R::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedType = $form->get('type')->getData();

            switch ($selectedType) {
                case 'adherent':
                    $user = new Adherent();
                    // Récupération des données
                    $poids = $form->get('poids')->getData();
                    $taille = $form->get('taille')->getData();
                    $age = $form->get('age')->getData();
                    $genre = $form->get('genre')->getData();
                    $objectif = $form->get('objectifPersonnel')->getData();
                    $niveau = $form->get('niveauActivite')->getData();

                    // Vérifier que tous les champs requis sont renseignés
                    if ($poids === null || $taille === null || $age === null || $genre === null || $objectif === null || $niveau === null) {
                        $this->addFlash('error', 'Tous les champs spécifiques à l\'adhérent doivent être renseignés.');
                        return $this->redirectToRoute('app_r');
                    }

                    $user->setPoids((float) $poids);
                    $user->setTaille((float) $taille);
                    $user->setAge((int) $age);
                    $user->setGenre($genre);
                    $user->setObjectifPersonnel($objectif);
                    $user->setNiveauActivite($niveau);
                    break;

                case 'coach':
                    $user = new Coach();
                    $user->setAnneeExperience((int) $form->get('anneeExperience')->getData());
                    $user->setCertificat_valide($form->get('Certificat_valide')->getData());
                    $user->setSpecialite($form->get('specialite')->getData());
                    $user->setCv($form->get('cv')->getData());
                    break;
                case 'createur_evenement':
                    $user = new CreateurEvenement();
                    $user->setNomOrganisation($form->get('nomOrganisation')->getData());
                    $user->setDescriptionCreateur($form->get('descriptionCreateur')->getData());
                    $user->setAdresseCreateur($form->get('adresseCreateur')->getData());
                    $user->setTelephoneCreateur($form->get('telephoneCreateur')->getData());
                    $user->setCertificatValide($form->get('certificatValide')->getData());
                    break;
                case 'investisseur_produit':
                    $user = new InvestisseurProduit();
                    $user->setNomEntreprise($form->get('nomEntreprise')->getData());
                    $user->setDescriptionInvestisseur($form->get('descriptionInvestisseur')->getData());
                    $user->setAdresseInvestisseur($form->get('adresseInvestisseur')->getData());
                    $user->setTelephoneInvestisseur($form->get('telephoneInvestisseur')->getData());
                    $user->setCertificatValide($form->get('certificatValideInvestisseur')->getData());
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

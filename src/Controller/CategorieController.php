<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;

final class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }
    #[Route('/afficher', name : 'app_afficher_categorie' )]
    public function afficherCategorie(CategorieRepository $rep) : Response
    {
        $categories = $rep->findAll();
        return $this->render("categorie/index.html.twig",
            ["categories"=>$categories]);
    }
    #[Route('/ajouter', name: 'app_ajouter_categorie')]
    public function AjouterCategorie(ManagerRegistry $doctrine, Request $request): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie, [
            'validation_groups' => ['creation'] // Activer le groupe creation
        ]);
        $form->handleRequest($request);

        // Vérification du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $categorie->setImage($form->get('imageFile')->getData()->getClientOriginalName());
            $em = $doctrine->getManager();
            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Catégorie ajoutée avec succès.');
            return $this->redirectToRoute('app_afficher_categorie');
        }

        return $this->renderForm('categorie/ajouterCategorie.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/supprimer/{id}',name:'app_supprimer_categorie')]
    public function SupprimerCategorie($id,CategorieRepository $repocateg,ManagerRegistry $doctrine): Response
    {
        $categorie=$repocateg->find($id);
        $em=$doctrine->getManager();
        $em->remove($categorie);
        $em->flush();
        return $this->redirectToRoute('app_afficher_categorie');
    }
    #[Route('/modifier/{id}', name: 'app_modifier_categorie')]
    public function ModifierCategorie(Request $request, EntityManagerInterface $em, CategorieRepository $repo, $id): Response
    {
        $categorie = $repo->find($id);
        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie non trouvée.');
        }
        // Conservez l'ancienne image
        $oldImage = $categorie->getImage();

        // Pour la modification, on utilise le groupe "Update" afin de ne pas requérir le champ imageFile.
        $form = $this->createForm(CategorieType::class, $categorie, [
            'validation_groups' => ['Update']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                // Si l'utilisateur fournit un nouveau fichier, utiliser le nom du fichier uploadé.
                $categorie->setImage($imageFile->getClientOriginalName());
            } else {
                // Sinon, conserver l'image existante.
                $categorie->setImage($oldImage);
            }

            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès.');
            return $this->redirectToRoute('app_afficher_categorie');
        }

        return $this->renderForm('categorie/modifierCategorie.html.twig', [
            'f' => $form->createView(),
            'categorie' => $categorie
        ]);
    }
}

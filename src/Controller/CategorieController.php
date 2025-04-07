<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du nom de l'image depuis le champ personnalisé "imageName"
            $imageName = $request->request->get('imageName');

            if ($imageName)
                $categorie->setImage($imageName);

            $em = $doctrine->getManager();
            $em->persist($categorie);
            $em->flush();
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
    public function ModifierCategorie(ManagerRegistry $doctrine, Request $request, $id, CategorieRepository $repocategorie): Response
    {
        $Categorie = $repocategorie->find($id);
        $oldImage = $Categorie->getImage(); // Récupérer l'ancienne image
        $form = $this->createForm(CategorieType::class, $Categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la valeur du champ personnalisé "imageName" depuis la requête
            $newImageName = $request->request->get('imageName', '');

            if ($newImageName) {
                $Categorie->setImage($newImageName);
            } else {
                $Categorie->setImage($oldImage);
            }

            $em = $doctrine->getManager();
            $em->persist($Categorie);
            $em->flush();

            return $this->redirectToRoute('app_afficher_categorie');
        }

        return $this->renderForm('categorie/modifierCategorie.html.twig', [
            'f' => $form->createView(),
            'categorie' => $Categorie
        ]);
    }
}

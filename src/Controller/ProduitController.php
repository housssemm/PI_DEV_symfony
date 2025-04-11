<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(): Response
    {
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }
    #[Route('/afficherProduit', name : 'app_afficher_produit' )]
    public function afficherProduit(ProduitRepository $rep) : Response
    {
        $produits = $rep->findAll();
        return $this->render("produit/index.html.twig",
            ["produits"=>$produits]);
    }
    #[Route('/ajouterProduit', name: 'app_ajouter_produit')]
    public function AjouterProduit(ManagerRegistry $doctrine, Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du nom de l'image depuis le champ personnalisé "imageName"
            $imageName = $request->request->get('imageName');
            if ($imageName)
                $produit->setImage($imageName);

            $em = $doctrine->getManager();
            $em->persist($produit);
            $em->flush();
            return $this->redirectToRoute('app_afficher_produit');
        }
        return $this->renderForm('produit/ajouterProduit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/supprimerProduit/{id}',name:'app_supprimer_produit')]
    public function SupprimerProduit($id,ProduitRepository $repoproduit,ManagerRegistry $doctrine): Response
    {
        $categorie=$repoproduit->find($id);
        $em=$doctrine->getManager();
        $em->remove($categorie);
        $em->flush();
        return $this->redirectToRoute('app_afficher_produit');
    }
    #[Route('/modifierProduit/{id}', name: 'app_modifier_produit')]
    public function ModifierProduit(ManagerRegistry $doctrine, Request $request, $id, ProduitRepository $repoproduit): Response
    {
        $Produit = $repoproduit->find($id);
        $oldImage = $Produit->getImage(); // Récupérer l'ancienne image
        $form = $this->createForm(ProduitType::class, $Produit );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la valeur du champ personnalisé "imageName" depuis la requête
            $newImageName = $request->request->get('imageName', '');

            if ($newImageName) {
                $Produit->setImage($newImageName);
            } else {
                $Produit->setImage($oldImage);
            }
            $em = $doctrine->getManager();
            $em->persist($Produit);
            $em->flush();

            return $this->redirectToRoute('app_afficher_produit');
        }

        return $this->render('produit/modifierProduit.html.twig', [
            'f' => $form->createView(),
            'produit' => $Produit
        ]);
    }
    #[Route('/recherche-produits', name: 'recherche_produits',methods: ['GET'])]
    public function search(Request $request, ProduitRepository $produitRepository)
    {
        $searchTerm = $request->query->get('searchTerm');

        // Si la recherche est vide, on récupère tous les produits
        if (empty($searchTerm)) {
            $produits = $produitRepository->findAll();
        } else {
            $produits = $produitRepository->findBySearchTerm($searchTerm);
        }

        return $this->render('produit/_produits.html.twig', [
            'produits' => $produits,
        ]);
    }
}

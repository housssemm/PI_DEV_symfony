<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    #[Route('/afficherProduitAdmin', name: 'app_afficher_produitAdmin')]
    public function afficherProduitAdmin(ProduitRepository $produitRepository): Response
    {
        // Get all products
        $produits = $produitRepository->findAll();

        // Get statistics by category
        $statsByCategory = $produitRepository->createQueryBuilder('p')
            ->select('c.nom AS categorie, COUNT(p.id) AS nombre')
            ->leftJoin('p.categorie', 'c')
            ->groupBy('c.nom')
            ->getQuery()
            ->getResult();

        return $this->render('produit/AdminProduit.html.twig', [
            'produits' => $produits,
            'statsByCategory' => $statsByCategory
        ]);
    }
    #[Route('/ajouterProduit', name: 'app_ajouter_produit')]
    public function AjouterProduit(ManagerRegistry $doctrine, Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit, [
            'validation_groups' => ['creation']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du nom de l'image
            $produit->setImage($form->get('imageFile')->getData()->getClientOriginalName());

            $investisseur = $this->getUser();
            $produit->setInvestisseurproduit($investisseur);

            $em = $doctrine->getManager();
            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit ajoutée avec succès.');
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

        $this->addFlash('success', 'Produit supprimé avec succès.');
        return $this->redirectToRoute('app_afficher_produit');
    }
    #[Route('/supprimerProduitAdmin/{id}', name: 'app_supprimer_produitAdmin')]
    public function SupprimerProduitAdmin($id, ProduitRepository $repoproduit, ManagerRegistry $doctrine): Response
    {
        // Récupérer le produit par son ID
        $produit = $repoproduit->find($id);
        // Vérifier si le produit existe
        if (!$produit) {
            $this->addFlash('error', 'Produit non trouvé.');
            return $this->redirectToRoute('app_afficher_produitAdmin');
        }
        // Récupérer le gestionnaire d'entités
        $em = $doctrine->getManager();
        // Supprimer le produit
        $em->remove($produit);
        $em->flush();
        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('app_afficher_produitAdmin');
    }

    #[Route('/modifierProduit/{id}', name: 'app_modifier_produit')]
    public function ModifierProduit(ManagerRegistry $doctrine, Request $request, $id, ProduitRepository $repoproduit): Response
    {
        $Produit = $repoproduit->find($id);
        if (!$Produit) {
            throw $this->createNotFoundException('Produit non trouvée.');
        }
        $oldImage = $Produit->getImage();
        $form = $this->createForm(ProduitType::class, $Produit,[
        'validation_groups' => ['Update']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $Produit->setImage($imageFile->getClientOriginalName());
            } else {
                $Produit->setImage($oldImage);
            }
            $investisseur = $this->getUser();
            $Produit->setInvestisseurproduit($investisseur);

            $em = $doctrine->getManager();
            $em->persist($Produit);
            $em->flush();

            $this->addFlash('success', 'Produit modifiée avec succès.');
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

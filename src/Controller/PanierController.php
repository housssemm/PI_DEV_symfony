<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Panierproduit;
use App\Entity\Produit;
use App\Form\PanierType;
use App\Repository\CategorieRepository;
use App\Repository\PanierproduitRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(): Response
    {
        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
        ]);
    }
    #[Route('/pageAdherent', name: 'app_afficher_panier')]
    public function afficher_Categories_Produits(CategorieRepository $repocateg, ProduitRepository $repoprod, Request $request): Response
    {
        // Récupération des catégories
        $categories = $repocateg->findAll();

        // Si une catégorie est sélectionnée (via l'ID de catégorie dans l'URL)
        $categorieId = $request->query->get('categorie_id');
        if ($categorieId) {
            // Récupération des produits filtrés par catégorie
            $categorie = $repocateg->find($categorieId);
            $produits = $repoprod->findBy(['categorie' => $categorie]);
        } else {
            // Si aucune catégorie n'est sélectionnée, on récupère tous les produits
            $produits = $repoprod->findAll();
        }

        // Retourner la vue avec les variables nécessaires
        return $this->render("panier/index.html.twig", [
            'categories' => $categories,
            'produits' => $produits,
            'categorieId' => $categorieId, // Passer l'ID de la catégorie active
        ]);
    }
    #[Route('/Voirproduit/{id}', name: 'produit_details')]
    public function voirDetails(Produit $produit): Response
    {
        return $this->render('panier/VoirDetails.html.twig', [
            'produit' => $produit,
        ]);
    }
    #[Route('/ajouterAuPanier', name: 'ajouter_au_panier')]
    public function ajouterAuPanier(PanierRepository $panierRepo, ManagerRegistry $doctrine): Panier
    {
        $adherent = $this->getUser();
        $panier = $panierRepo->findOneBy(['user' => $adherent]);
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($adherent);

            $em = $doctrine->getManager();
            $em->persist($panier);
            $em->flush();
        }
        return $panier;
    }
    #[Route('/ajouterProduitAuPanier/{id}', name: 'ajouter_produit_au_panier')]
    public function ajouterProduitAuPanier(Produit $produit, Request $request, PanierRepository $panierRepo, PanierProduitRepository $panierProduitRepo, ManagerRegistry $doctrine): Response {
        $form = $this->createForm(PanierType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();

            // Récupérer le panier de l'utilisateur
            $panier = $this->ajouterAuPanier($panierRepo, $doctrine);

            // Récupérer la quantité choisie
            $quantiteChoisie = $form->get('quantite')->getData();

            // Vérifier si le produit est déjà dans le panier
            $panierProduit = $panierProduitRepo->findOneBy(['panier' => $panier, 'produit' => $produit]);

            if ($panierProduit) {
                $panierProduit->setQuantite($panierProduit->getQuantite() + $quantiteChoisie);
            } else {
                $panierProduit = new PanierProduit();
                $panierProduit->setPanier($panier);
                $panierProduit->setProduit($produit);
                $panierProduit->setQuantite($quantiteChoisie);
                $panierProduit->setDate(new \DateTime());
                $panierProduit->setMontant($produit->getPrix() * $quantiteChoisie);
                $panierProduit->setEtatPaiement('En_Attente');

                $em->persist($panierProduit);
            }
            $em->flush();

            $this->addFlash('success', 'Produit ajouté au panier avec succès !');

            return $this->redirectToRoute('app_afficher_panier');
        }
        return $this->render('panier/VoirDetails.html.twig', [
            'produit' => $produit,
            'f' => $form->createView(),
        ]);
    }
    #[Route('/afficherProduitAuPanier', name: 'afficher_produit_au_panier')]
    public function afficherPanier(PanierProduitRepository $panierProduitRepository, PanierRepository $panierRepository): Response
    {
        $user = $this->getUser();

        // Récupérer le panier
        $panier = $panierRepository->findPanierByUser($user);

        if (!$panier) {
            return $this->render('panier/afficher.html.twig', [
                'message' => 'Votre panier est vide.',
            ]);
        }

        // Récupérer les produits associés à ce panier
        $produitsPanier = $panierProduitRepository->findBy(['panier' => $panier]);

        $montantTotal = 0;
        foreach ($produitsPanier as $produitPanier) {
            $montantTotal += $produitPanier->getQuantite() * $produitPanier->getProduit()->getPrix();
        }

        return $this->render('panier/VoirPanier.html.twig', [
            'produitsPanier' => $produitsPanier,
            'montantTotal' => $montantTotal,
        ]);
    }
}

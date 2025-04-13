<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
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
}

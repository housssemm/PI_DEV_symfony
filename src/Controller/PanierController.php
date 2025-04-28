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
use App\Service\WishlistService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    public function afficher_Categories_Produits(CategorieRepository $repocateg,ProduitRepository $repoprod,PanierRepository $panierRepository, PanierProduitRepository $panierProduitRepository, WishlistService $wishlistService, Request $request): Response {
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

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer le panier de l'utilisateur
        $panier = $panierRepository->findPanierByUser($user);

        if (!$panier) {
            // Si le panier est vide ou inexistant
            return $this->render('panier/afficher.html.twig', [
                'categories' => $categories,
                'produits' => $produits,
                'message' => 'Votre panier est vide.',
            ]);
        }

        // Récupérer les produits associés au panier
        $produitsPanier = $panierProduitRepository->findBy(['panier' => $panier]);

        // Récupérer les produits de la wishlist
        $wishlistIds = $wishlistService->getWishlist();
        $produitsWishlist = $repoprod->findBy(['id' => $wishlistIds]);


        $montantTotal = 0;
        foreach ($produitsPanier as $produitPanier) {
            $montantTotal += $produitPanier->getQuantite() * $produitPanier->getProduit()->getPrix();
        }

        // Retourner la vue avec les variables nécessaires
        return $this->render('panier/index.html.twig', [
            'categories' => $categories,
            'produits' => $produits,
            'produitsPanier' => $produitsPanier,
            'montantTotal' => $montantTotal,
            'produitsWishlist' => $produitsWishlist,
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
    public function ajouterProduitAuPanier(Produit $produit, Request $request, PanierRepository $panierRepo, PanierProduitRepository $panierProduitRepo, ManagerRegistry $doctrine, HttpClientInterface $client): Response
    {
        // Création du formulaire pour ajouter au panier
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

        // Appeler le service Python pour obtenir les recommandations
        $recommendations = [];
        try {
            $response = $client->request('GET', 'http://127.0.0.1:5000/recommender/' . $produit->getId());

            if ($response->getStatusCode() === 200) {
                $recommendations = $response->toArray();
            }
        } catch (\Exception $e) {
            // Gestion des erreurs si l'API Python n'est pas accessible
            $this->addFlash('error', 'Impossible de récupérer les recommandations pour le moment.');
        }

        return $this->render('panier/VoirDetails.html.twig', [
            'produit' => $produit,
            'f' => $form->createView(),
            'recommendations' => $recommendations,  // Passer les recommandations à la vue
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
    #[Route('/supprimerProduitPanier/{id}', name: 'supprimer_produit_panier')]
    public function supprimerProduitPanier(int $id,PanierProduitRepository $panierProduitRepository,PanierRepository $panierRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un produit du panier.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le panier de l'utilisateur
        $panier = $panierRepository->findPanierByUser($user);

        if (!$panier) {
            $this->addFlash('error', 'Aucun panier trouvé.');
            return $this->redirectToRoute('app_afficher_panier');
        }

        // Récupérer le produit du panier à supprimer
        $produitPanier = $panierProduitRepository->findOneBy(['id' => $id, 'panier' => $panier]);

        if (!$produitPanier) {
            $this->addFlash('error', 'Produit non trouvé dans le panier.');
            return $this->redirectToRoute('app_afficher_panier');
        }

        // Supprimer le produit du panier
        $entityManager->remove($produitPanier);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé du panier avec succès.');

        return $this->redirectToRoute('app_afficher_panier');
    }
    #[Route('/modifierProduitPanier/{id}', name: 'modifier_produit_panier')]
    public function modifierQuantite($id, Request $request, PanierRepository $panierRepository, PanierProduitRepository $panierProduitRepository,EntityManagerInterface $entityManager)
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer le panier de l'utilisateur
        $panier = $panierRepository->findPanierByUser($user);

        if (!$panier) {
            throw $this->createNotFoundException('Panier non trouvé');
        }

        // Récupérer le produitPanier depuis la base de données par son ID et le panier associé
        $produitPanier = $panierProduitRepository->findOneBy(['id' => $id, 'panier' => $panier]);

        if (!$produitPanier) {
            throw $this->createNotFoundException('Produit non trouvé dans le panier');
        }

        // Vérifier l'action (plus ou moins)
        $action = $request->request->get('action');

        if ($action === 'plus') {
            $produitPanier->setQuantite($produitPanier->getQuantite() + 1);
        } elseif ($action === 'minus' && $produitPanier->getQuantite() > 1) {
            $produitPanier->setQuantite($produitPanier->getQuantite() - 1);
        }

        // Sauvegarder la modification dans la base de données
        $entityManager->flush();

        $this->addFlash(
            'success',
            sprintf(
                'La quantité du produit "%s" a été modifiée avec succès à %d.',
                $produitPanier->getProduit()->getNom(),
                $produitPanier->getQuantite()
            )
        );
        // Rediriger vers la page du panier après la modification
        return $this->redirectToRoute('afficher_produit_au_panier');
    }

    #[Route('/wishlist/add/{id}', name: 'ajouter_produit_en_wishlist', methods: ["GET"])]
    public function ajouterProduitWishlist(int $id, WishlistService $wishlistService): Response
    {
        $wishlistService->ajouter($id);

        $this->addFlash('success', 'Produit ajouté à votre liste de souhaits !');
        return $this->redirectToRoute('app_afficher_panier', [
            'wishlistIds' => implode(',', $wishlistService->getWishlist()),
        ]);
    }
    #[Route('/wishlist', name: 'voir_wishlist', methods: ["GET"])]
    public function voirWishlist(WishlistService $wishlistService, ProduitRepository $produitRepository): Response
    {
        $wishlistIds = $wishlistService->getWishlist();
        $produits = $produitRepository->findBy(['id' => $wishlistIds]);

        return $this->render('wishlist/index.html.twig', [
            'produits' => $produits,
        ]);
    }
    #[Route('/wishlist/remove/{id}', name: 'supprimer_produit_de_wishlist', methods: ["GET"])]
    public function supprimerProduitWishlist(int $id, WishlistService $wishlistService): Response
    {
        $wishlistService->supprimer($id);

        $this->addFlash('success', 'Produit supprimé de votre liste de souhaits !');
        return $this->redirectToRoute('voir_wishlist');
    }
}

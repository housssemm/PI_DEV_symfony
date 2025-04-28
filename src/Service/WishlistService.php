<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class WishlistService
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        // Récupère la session courante depuis le RequestStack
        $this->session = $requestStack->getSession();
    }

    public function ajouter(int $produitId): void
    {
        $wishlist = $this->session->get('wishlist', []);
        if (!in_array($produitId, $wishlist, true)) {
            $wishlist[] = $produitId;
        }
        $this->session->set('wishlist', $wishlist);
    }

    public function getWishlist(): array
    {
        return $this->session->get('wishlist', []);
    }

    public function supprimer(int $produitId): void
    {
        $wishlist = $this->session->get('wishlist', []);
        $wishlist = array_diff($wishlist, [$produitId]);
        $this->session->set('wishlist', $wishlist);
    }
}

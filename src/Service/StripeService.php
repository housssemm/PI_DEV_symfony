<?php

namespace App\Service;

use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private string $stripeSecretKey;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(string $stripeSecretKey, UrlGeneratorInterface $urlGenerator)
    {
        $this->stripeSecretKey = $stripeSecretKey;
        $this->urlGenerator = $urlGenerator;
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Crée un Payment Intent pour un paiement direct.
     *
     * @param float $amount Montant à débiter (en dollars)
     * @param string $currency Devise (par défaut 'usd')
     * @param array $metadata Métadonnées supplémentaires (ex. planningId, adherentId)
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent(float $amount, string $currency = 'usd', array $metadata = []): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $amount * 100, // Convertir en centimes (cents pour USD)
            'currency' => $currency, // Utiliser 'usd' par défaut
            'payment_method_types' => ['card'],
            'metadata'             => $metadata,
        ]);
    }

    /**
     * Récupérer un Payment Intent.
     *
     * @param string $paymentIntentId ID du Payment Intent
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }
}
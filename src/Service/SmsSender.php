<?php

namespace App\Service;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class SmsSender
{
    private $client;
    private $fromNumber;

    public function __construct(string $sid, string $authToken, string $fromNumber)
    {
        $this->client = new Client($sid, $authToken);
        $this->fromNumber = $fromNumber;
    }

    public function sendSms(string $toNumber, string $message): bool
    {
        try {
            $this->client->messages->create(
                $toNumber, // Numéro au format international, ex: +33612345678
                [
                    'from' => $this->fromNumber,
                    'body' => $message,
                ]
            );
            return true;
        } catch (TwilioException $e) {
            // Log l'erreur (utilise un logger si configuré)
            error_log('Erreur Twilio: ' . $e->getMessage());
            return false;
        }
    }
}
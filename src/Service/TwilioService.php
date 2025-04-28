<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Twilio\Rest\Client;

class TwilioService
{
    private Client $client;
    private string $fromNumber;
    private LoggerInterface $logger;

    public function __construct(string $accountSid, string $authToken, string $fromNumber, LoggerInterface $logger)
    {
        $this->client = new Client($accountSid, $authToken);
        $this->fromNumber = $fromNumber;
        $this->logger = $logger;
    }

    public function sendSms(string $to, string $body): string
    {
        $message = $this->client->messages->create(
            $to,
            [
                'from' => $this->fromNumber,
                'body' => $body,
            ]
        );

        // Loguer les détails du message
        $this->logger->info('Statut du SMS envoyé via API Twilio : ' . $message->status);
        $this->logger->info('SID du message : ' . $message->sid);

        return $message->sid; // Retourner le SID pour un suivi ultérieur si nécessaire
    }
}
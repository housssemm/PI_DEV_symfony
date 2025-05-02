<?php

namespace App\Service;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;

class TwilioService
{
    private $client;
    private $fromNumber;
    private $logger;

    public function __construct(string $accountSid, string $authToken, string $fromNumber, LoggerInterface $logger)
    {
        $this->client = new Client($accountSid, $authToken);
        $this->fromNumber = $fromNumber;
        $this->logger = $logger;
    }

    public function sendSms(string $to, string $message): string
    {
        $this->logger->info('Tentative d’envoi SMS à : ' . $to . ' avec message : ' . $message);
        try {
            $message = $this->client->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message,
            ]);
            $this->logger->info('SMS envoyé avec SID : ' . $message->sid);
            return $message->sid;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l’envoi SMS à ' . $to . ' : ' . $e->getMessage() . ' (Code : ' . $e->getCode() . ')');
            throw new \Exception('Échec de l’envoi SMS : ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
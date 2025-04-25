<?php

namespace App\Service;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class SmsSender
{
    private Client $client;
    private string $from;

    public function __construct(string $twilioSid, string $twilioToken, string $from)
    {
        $this->client = new Client($twilioSid, $twilioToken);
        $this->from = $from;
    }

    /**
     * Envoie un SMS via l'API Twilio.
     *
     * @param string $to      Numéro de téléphone du destinataire au format E.164 (ex: +216xxxxxxxx)
     * @param string $message Contenu du message SMS
     * @throws TwilioException En cas d'erreur lors de l'envoi du SMS
     */
    public function send(string $to, string $message): void
    {
        try {
            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message,
            ]);
        } catch (TwilioException $e) {
            throw new TwilioException('Erreur lors de l\'envoi du SMS : ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
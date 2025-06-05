<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use App\Service\SmsSender;

class WaterReminderController extends AbstractController
{
    private $params;
    private $logger;
    private $smsSender;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger, SmsSender $smsSender)
    {
        $this->params = $params;
        $this->logger = $logger;
        $this->smsSender = $smsSender;
    }

    #[Route('/water-reminder', name: 'app_water_reminder')]
    public function index(): Response
    {
        return $this->render('water_reminder/index.html.twig', [
            'controller_name' => 'WaterReminderController',
        ]);
    }

    #[Route('/water-reminder/schedule', name: 'app_water_reminder_schedule', methods: ['POST'])]
    public function scheduleReminder(Request $request): JsonResponse
    {
        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);
        $phone = $data['phone'] ?? null;

        if (!$phone) {
            return $this->json(['success' => false, 'message' => 'Numéro de téléphone manquant'], 400);
        }

        // Formater le numéro de téléphone si nécessaire
        if (!str_starts_with($phone, '+')) {
            $phone = '+216' . ltrim($phone, '0'); // Ajoutez le préfixe de votre pays
        }

        // Mode test - si l'environnement est dev ou si TWILIO_TEST_MODE est true
        $testMode = $this->getParameter('kernel.environment') === 'dev' || 
                   ($this->params->has('twilio_test_mode') && $this->params->get('twilio_test_mode') === true);

        if ($testMode) {
            $this->logger->info('SMS envoyé (MODE TEST) au numéro: ' . $phone);
            
            return $this->json([
                'success' => true,
                'message' => 'SMS envoyé avec succès (MODE TEST)',
                'sid' => 'TEST_SID_' . uniqid()
            ]);
        }

        try {
            // Utiliser le service SmsSender pour envoyer le SMS
            $message = 'Rappel d\'hydratation : N\'oubliez pas de boire de l\'eau ! 💧';
            $smsSent = $this->smsSender->sendSms($phone, $message);

            if ($smsSent) {
                $this->logger->info('SMS envoyé au numéro: ' . $phone);
                
                return $this->json([
                    'success' => true,
                    'message' => 'SMS envoyé avec succès',
                ]);
            } else {
                $this->logger->error('Échec de l\'envoi du SMS au numéro: ' . $phone);
                
                return $this->json([
                    'success' => false,
                    'message' => 'Échec de l\'envoi du SMS',
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi du SMS: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'phone' => $phone
            ]);
            
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS: ' . $e->getMessage(),
                'details' => 'Vérifiez la configuration Twilio dans votre fichier .env'
            ], 500);
        }
    }
}






<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;

class WorkoutDetectionController extends AbstractController
{
    #[Route('/workout-detection', name: 'app_workout_detection')]
    public function index(): Response
    {
        return $this->render('workout_detection/index.html.twig', [
            'video_url' => 'http://localhost:5000/video_feed',
        ]);
    }

    #[Route('/workout-detection/stats', name: 'app_workout_detection_stats')]
    public function stats(Request $request): Response
    {
        $client = HttpClient::create(['timeout' => 3]); // Réduire le timeout à 3 secondes
        $counterData = ['count' => 0, 'stage' => 'non disponible'];
        $error = null;
        
        try {
            $response = $client->request('GET', 'http://localhost:5000/counter');
            $counterData = $response->toArray();
        } catch (\Exception $e) {
            $error = 'Service de détection non disponible. Assurez-vous que le script Python est en cours d\'exécution.';
        }
        
        return $this->render('workout_detection/stats.html.twig', [
            'counter' => $counterData['count'] ?? 0,
            'stage' => $counterData['stage'] ?? 'non disponible',
            'error' => $error
        ]);
    }

    #[Route('/workout-detection/reset', name: 'app_workout_detection_reset')]
    public function reset(): Response
    {
        $client = HttpClient::create(['timeout' => 3]); // Réduire le timeout à 3 secondes
        
        try {
            $client->request('GET', 'http://localhost:5000/reset');
            $this->addFlash('success', 'Compteur réinitialisé avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Service de détection non disponible. Assurez-vous que le script Python est en cours d\'exécution.');
        }
        
        return $this->redirectToRoute('app_workout_detection');
    }
}


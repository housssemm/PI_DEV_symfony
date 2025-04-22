<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorkoutController extends AbstractController
{
    #[Route('/workout', name: 'workout')]
    public function index(): Response
    {
        return $this->render('planning/planningCoach.html.twig', [
            'video_url' => 'http://127.0.0.1:5000/video_feed'
        ]);
    }
}

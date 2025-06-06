<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OurIaController extends AbstractController
{

    #[Route('/our-ia', name: 'app_our_ia')]

    public function index(): Response
    {
        // Example cards data
        $cards = [
            [
                'title' => 'Biceps Curls AI Detector',
                'description' => 'Correct your Biceps Curls.',
                'route' => 'settings',
                'img' => 'img/biceps.png',
                'path' => 'app_ia_detection',
            ],
           
            [
                'title' => 'Squat AI Detector' ,
                'description' => 'Correct your Squat.',
                'route' => 'profile',
                'img' => 'img/squat.jpg',
                'path' => 'app_home_team',
            ],
            [
                'title' => 'Push-Ups AI Detector',
                'description' => 'Correct your Push-Ups.',
                'route' => 'dashboard',
                'img' => 'img/pmp.png',
                'path' => 'app_workout_detection',
            ],
            [
                'title' => 'IA Exercice Sport Generater',
                'description' => 'generate your exercices.',
                'route' => 'settings',
                'img' => 'img/ia_gen.png',
                'path' => 'app_gym',
            ],
            [
                'title' => 'IA Food Analysis',
                'description' => 'Rate And Know  Your Food Calorie.',
                'route' => 'settings',
                'img' => 'img/rec_ia.jpg',
                'path' => 'app_food_analysis',
            ],
            [
                'title' => 'IA Meals Generator',
                'description' => 'generate your meals with IA.',
                'route' => 'settings',
                'img' => 'img/IA_FF.png',
                'path' => 'app_nutrition',
            ],
        ];
        return $this->render('our_ia.html.twig', [
            'cards' => $cards,
        ]);
    }
}

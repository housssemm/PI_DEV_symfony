<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalorieCalculatorController extends AbstractController
{
    #[Route('/calorie-calculator', name: 'app_calorie_calculator')]
    public function index(Request $request): Response
    {
        $result = null;
        $dailyCalories = null;
        $macros = null;
        
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $gender = $request->request->get('gender');
            $age = (int)$request->request->get('age');
            $weight = (float)$request->request->get('weight');
            $height = (float)$request->request->get('height');
            $activityLevel = $request->request->get('activity_level');
            $goal = $request->request->get('goal');
            
            // Calculer le BMR (Basal Metabolic Rate) avec la formule de Mifflin-St Jeor
            if ($gender === 'male') {
                $bmr = 10 * $weight + 6.25 * $height - 5 * $age + 5;
            } else {
                $bmr = 10 * $weight + 6.25 * $height - 5 * $age - 161;
            }
            
            // Appliquer le multiplicateur d'activité
            $activityMultipliers = [
                'sedentary' => 1.2,
                'light' => 1.375,
                'moderate' => 1.55,
                'active' => 1.725,
                'very_active' => 1.9
            ];
            
            $tdee = $bmr * $activityMultipliers[$activityLevel];
            
            // Ajuster en fonction de l'objectif
            $goalAdjustments = [
                'lose' => 0.8, // Déficit de 20%
                'maintain' => 1.0, // Maintien
                'gain' => 1.15 // Surplus de 15%
            ];
            
            $dailyCalories = round($tdee * $goalAdjustments[$goal]);
            
            // Calculer les macronutriments recommandés
            $macros = [
                'protein' => [
                    'grams' => round($weight * ($goal === 'gain' ? 2.2 : ($goal === 'lose' ? 2.0 : 1.8))),
                    'calories' => round($weight * ($goal === 'gain' ? 2.2 : ($goal === 'lose' ? 2.0 : 1.8)) * 4)
                ],
                'carbs' => [
                    'grams' => 0,
                    'calories' => 0
                ],
                'fats' => [
                    'grams' => 0,
                    'calories' => 0
                ]
            ];
            
            // Calculer les graisses (25-30% des calories totales)
            $fatPercentage = $goal === 'lose' ? 0.3 : 0.25;
            $macros['fats']['calories'] = round($dailyCalories * $fatPercentage);
            $macros['fats']['grams'] = round($macros['fats']['calories'] / 9);
            
            // Le reste en glucides
            $macros['carbs']['calories'] = $dailyCalories - $macros['protein']['calories'] - $macros['fats']['calories'];
            $macros['carbs']['grams'] = round($macros['carbs']['calories'] / 4);
            
            $result = [
                'bmr' => round($bmr),
                'tdee' => round($tdee),
                'dailyCalories' => $dailyCalories
            ];
        }
        
        return $this->render('nutrition/calorie_calculator.html.twig', [
            'result' => $result,
            'dailyCalories' => $dailyCalories,
            'macros' => $macros
        ]);
    }
}
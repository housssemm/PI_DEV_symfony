<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class NutritionController extends AbstractController
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    #[Route('/nutrition', name: 'app_nutrition')]
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('goal', ChoiceType::class, [
                'choices' => [
                    'Perte de poids' => 'weight_loss',
                    'Prise de muscle' => 'muscle_gain',
                    'Maintien' => 'maintenance'
                ],
                'label' => 'Objectif',
                'attr' => ['class' => 'form-control']
            ])
            ->add('level', ChoiceType::class, [
                'choices' => [
                    'Débutant' => 'beginner',
                    'Intermédiaire' => 'intermediate',
                    'Avancé' => 'advanced'
                ],
                'label' => 'Niveau',
                'attr' => ['class' => 'form-control']
            ])
            ->add('age', NumberType::class, [
                'label' => 'Âge',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('weight', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('height', NumberType::class, [
                'label' => 'Taille (cm)',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('activity_level', ChoiceType::class, [
                'choices' => [
                    'Sédentaire' => 'sedentary',
                    'Légèrement actif' => 'lightly_active',
                    'Modérément actif' => 'moderately_active',
                    'Très actif' => 'very_active',
                    'Extrêmement actif' => 'extremely_active'
                ],
                'label' => 'Niveau d\'activité',
                'attr' => ['class' => 'form-control']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer le programme',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $mealPlan = $this->generateMealPlanWithGemini($data);
            return $this->render('nutrition/index.html.twig', [
                'form' => $form->createView(),
                'mealPlan' => $mealPlan
            ]);
        }

        return $this->render('nutrition/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function generateMealPlanWithGemini(array $data): array
    {
        $client = HttpClient::create();
        /*$apiKey = $this->params->get('gemini_api_key');*/
        $apiKey = $_ENV['GEMINI_API_KEY'];
        $prompt = $this->createPrompt($data);

        try {
            $response = $client->request('POST', 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $apiKey
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 2048,
                    ]
                ]
            ]);

            $result = json_decode($response->getContent(), true);
            return $this->parseGeminiResponse($result, $data);
        } catch (\Exception $e) {
            // Fallback to sample meal plan if API fails
            return $this->generateSampleMealPlan($data);
        }
    }

    private function createPrompt(array $data): string
    {
        return "Génère un programme nutritionnel personnalisé en français avec les caractéristiques suivantes:
        - Objectif: {$data['goal']}
        - Niveau: {$data['level']}
        - Âge: {$data['age']} ans
        - Poids: {$data['weight']} kg
        - Taille: {$data['height']} cm
        - Niveau d'activité: {$data['activity_level']}

        Le programme doit inclure:
        1. Un titre personnalisé
        2. 5 repas par jour (petit-déjeuner, collation matin, déjeuner, collation après-midi, dîner)
        3. Pour chaque repas:
           - Nom du repas
           - Heure suggérée
           - 2-3 aliments avec:
             * Nom de l'aliment
             * Calories
             * Protéines (g)
             * Glucides (g)
             * Lipides (g)
             * Image (nom du fichier .jpg)

        Format de réponse attendu en JSON:
        {
            'title': 'Titre du programme',
            'meals': [
                {
                    'name': 'Nom du repas',
                    'time': 'Heure',
                    'foods': [
                        {
                            'name': 'Nom de l\'aliment',
                            'calories': 'XXX',
                            'protein': 'XX',
                            'carbs': 'XX',
                            'fat': 'XX',
                            'img': 'nom_image.jpg'
                        }
                    ]
                }
            ]
        }";
    }

    private function parseGeminiResponse(array $response, array $data): array
    {
        try {
            $content = $response['candidates'][0]['content']['parts'][0]['text'];
            $mealPlan = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $mealPlan;
            }
        } catch (\Exception $e) {
            // If parsing fails, return sample meal plan
        }

        return $this->generateSampleMealPlan($data);
    }

    private function generateSampleMealPlan(array $data): array
    {
        $goal = $data['goal'];
        $level = $data['level'];

        $title = match ($goal) {
            'weight_loss' => 'Programme de Perte de Poids',
            'muscle_gain' => 'Programme de Prise de Muscle',
            default => 'Programme de Maintien'
        };

        return [
            'title' => $title,
            'meals' => [
                [
                    'name' => 'Petit-déjeuner',
                    'time' => '8:00',
                    'foods' => [
                        [
                            'name' => 'Porridge aux flocons d\'avoine',
                            'calories' => '350',
                            'protein' => '12',
                            'carbs' => '60',
                            'fat' => '6',
                            'img' => 'oatmeal.jpg'
                        ],
                        [
                            'name' => 'Smoothie protéiné',
                            'calories' => '250',
                            'protein' => '20',
                            'carbs' => '30',
                            'fat' => '5',
                            'img' => 'smoothie.jpg'
                        ],
                        [
                            'name' => 'Œufs brouillés',
                            'calories' => '180',
                            'protein' => '15',
                            'carbs' => '2',
                            'fat' => '12',
                            'img' => 'scrambled-eggs.jpg'
                        ],
                        [
                            'name' => 'Pain complet',
                            'calories' => '120',
                            'protein' => '4',
                            'carbs' => '22',
                            'fat' => '2',
                            'img' => 'whole-bread.jpg'
                        ]
                    ]
                ],
                [
                    'name' => 'Collation matin',
                    'time' => '11:00',
                    'foods' => [
                        [
                            'name' => 'Yaourt grec avec fruits',
                            'calories' => '200',
                            'protein' => '15',
                            'carbs' => '20',
                            'fat' => '8',
                            'img' => 'yogurt.jpg'
                        ],
                        [
                            'name' => 'Banane',
                            'calories' => '105',
                            'protein' => '1.3',
                            'carbs' => '27',
                            'fat' => '0.3',
                            'img' => 'banana.jpg'
                        ],
                        [
                            'name' => 'Amandes',
                            'calories' => '160',
                            'protein' => '6',
                            'carbs' => '6',
                            'fat' => '14',
                            'img' => 'almonds.jpg'
                        ]
                    ]
                ],
                [
                    'name' => 'Déjeuner',
                    'time' => '13:00',
                    'foods' => [
                        [
                            'name' => 'Poulet grillé',
                            'calories' => '300',
                            'protein' => '35',
                            'carbs' => '0',
                            'fat' => '15',
                            'img' => 'chicken.jpg'
                        ],
                        [
                            'name' => 'Salade de légumes',
                            'calories' => '150',
                            'protein' => '5',
                            'carbs' => '20',
                            'fat' => '8',
                            'img' => 'salad.jpg'
                        ],
                        [
                            'name' => 'Quinoa',
                            'calories' => '220',
                            'protein' => '8',
                            'carbs' => '39',
                            'fat' => '4',
                            'img' => 'quinoa.jpg'
                        ],
                        [
                            'name' => 'Avocat',
                            'calories' => '160',
                            'protein' => '2',
                            'carbs' => '8.5',
                            'fat' => '15',
                            'img' => 'avocado.jpg'
                        ]
                    ]
                ],
                [
                    'name' => 'Collation après-midi',
                    'time' => '16:00',
                    'foods' => [
                        [
                            'name' => 'Mélange de noix',
                            'calories' => '280',
                            'protein' => '10',
                            'carbs' => '15',
                            'fat' => '22',
                            'img' => 'nuts.jpg'
                        ],
                        [
                            'name' => 'Pomme',
                            'calories' => '95',
                            'protein' => '0.5',
                            'carbs' => '25',
                            'fat' => '0.3',
                            'img' => 'apple.jpg'
                        ],
                        [
                            'name' => 'Barre protéinée',
                            'calories' => '200',
                            'protein' => '20',
                            'carbs' => '15',
                            'fat' => '7',
                            'img' => 'protein-bar.jpg'
                        ],
                        [
                            'name' => 'Carottes',
                            'calories' => '50',
                            'protein' => '1',
                            'carbs' => '12',
                            'fat' => '0.3',
                            'img' => 'carrots.jpg'
                        ]
                    ]
                ],
                [
                    'name' => 'Dîner',
                    'time' => '19:00',
                    'foods' => [
                        [
                            'name' => 'Saumon grillé',
                            'calories' => '350',
                            'protein' => '40',
                            'carbs' => '0',
                            'fat' => '20',
                            'img' => 'salmon.jpg'
                        ],
                        [
                            'name' => 'Riz brun',
                            'calories' => '220',
                            'protein' => '5',
                            'carbs' => '45',
                            'fat' => '2',
                            'img' => 'brown-rice.jpg'
                        ],
                        [
                            'name' => 'Brocoli vapeur',
                            'calories' => '55',
                            'protein' => '3.7',
                            'carbs' => '11.2',
                            'fat' => '0.6',
                            'img' => 'broccoli.jpg'
                        ],
                        [
                            'name' => 'Patate douce',
                            'calories' => '180',
                            'protein' => '2',
                            'carbs' => '41',
                            'fat' => '0.3',
                            'img' => 'sweet-potato.jpg'
                        ],
                        [
                            'name' => 'Haricots verts',
                            'calories' => '44',
                            'protein' => '2.4',
                            'carbs' => '9.8',
                            'fat' => '0.2',
                            'img' => 'green-beans.jpg'
                        ]
                    ]
                ],
                [
                    'name' => 'Collation soir (optionnel)',
                    'time' => '21:00',
                    'foods' => [
                        [
                            'name' => 'Fromage blanc',
                            'calories' => '120',
                            'protein' => '20',
                            'carbs' => '8',
                            'fat' => '3',
                            'img' => 'cottage-cheese.jpg'
                        ],
                        [
                            'name' => 'Fruits rouges',
                            'calories' => '70',
                            'protein' => '1',
                            'carbs' => '15',
                            'fat' => '0.5',
                            'img' => 'berries.jpg'
                        ],
                        [
                            'name' => 'Chocolat noir',
                            'calories' => '150',
                            'protein' => '2',
                            'carbs' => '13',
                            'fat' => '10',
                            'img' => 'dark-chocolate.jpg'
                        ],
                        [
                            'name' => 'Thé vert',
                            'calories' => '0',
                            'protein' => '0',
                            'carbs' => '0',
                            'fat' => '0',
                            'img' => 'green-tea.jpg'
                        ]
                    ]
                ]
            ]
        ];
    }
}

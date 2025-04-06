<?php
//
//
//namespace App\Controller;
//
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpClient\HttpClient;
//
//class GymProgramAiController extends AbstractController
//{
//    #[Route('/gym', name: 'app_gym')]
//    public function index(Request $request): Response
//    {
//        $program = null;
//
//        if ($request->isMethod('POST')) {
//            $data = $request->request->all();
//
//            $prompt = sprintf(
//                "Tu es un coach sportif. Crée un programme de gym personnalisé pour :
//                - Sexe : %s
//                - Âge : %s ans
//                - Taille : %s cm
//                - Poids : %s kg
//                - Objectif : %s
//                - Niveau : %s
//                - Jours par semaine : %s
//                - Équipement : %s
//                Format : tableau clair avec jour / exercice / séries / répétitions.",
//                $data['gender'],
//                $data['age'],
//                $data['height'],
//                $data['weight'],
//                $data['goal'],
//                $data['level'],
//                $data['daysPerWeek'],
//                $data['equipment']
//            );
//
//            $geminiApiKey = $_ENV['GEMINI_API_KEY'];
//
//            try {
//                $httpClient = HttpClient::create();
//
//                // Attempt to use Gemini 1.5 Flash
//                $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent';
//
//                // Recommended: Consider migrating to Gemini 2.0 Flash
//                // $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';
//
//                $response = $httpClient->request('POST', $apiUrl, [
//                    'headers' => [
//                        'Content-Type' => 'application/json',
//                    ],
//                    'query' => [
//                        'key' => $geminiApiKey,
//                    ],
//                    'json' => [
//                        'contents' => [
//                            [
//                                'parts' => [
//                                    ['text' => $prompt]
//                                ]
//                            ]
//                        ],
//                        'generationConfig' => [
//                            'temperature' => 0.7,
//                            'maxOutputTokens' => 2048,
//                        ]
//                    ],
//                ]);
//
//                $statusCode = $response->getStatusCode();
//
//                if ($statusCode === 200) {
//                    $responseData = $response->toArray();
//
//                    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
//                        $program = $responseData['candidates'][0]['content']['parts'][0]['text'];
//                    } else {
//                        $program = 'Réponse reçue mais format inattendu.';
//                        error_log('Unexpected response format: ' . json_encode($responseData));
//                    }
//                } else {
//                    $errorDetails = $response->getContent(false);
//                    $program = 'Erreur lors de la communication avec Gemini: Code ' . $statusCode . '. Détails: ' . $errorDetails;
//                    error_log('Gemini API Error: Status ' . $statusCode . ', Details: ' . $errorDetails);
//                }
//            } catch (\Exception $e) {
//                $program = 'Erreur lors de la communication avec Gemini: ' . $e->getMessage();
//                error_log('Gemini API Error Exception: ' . $e->getMessage());
//            }
//        }
//
//        return $this->render('gymAi/index.html.twig', [
//            'program' => $program,
//        ]);
//    }
//}



namespace App\Controller;

use App\Form\GymProgramType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;

class GymProgramAiController extends AbstractController
{
    #[Route('/gym', name: 'app_gym')]
    public function index(Request $request): Response
    {
        $program = null;
        $form = $this->createForm(GymProgramType::class);
        $form->handleRequest($request);

        // Initialise $images ici, en dehors du bloc if
        $images = [
            'Marche rapide' => 'images/exercises/marche_rapide.gif',
            'Squats' => 'images/exercises/squats.gif',
            'Pompes' => 'images/exercises/pompes.gif',
            'Pompes (sur les genoux si besoin)' => 'images/exercises/pompes_genoux.gif',
            'Planche' => 'images/exercises/planche.gif',
            'Fentes (alternées)' => 'images/exercises/fentes.gif',
            'Burpees' => 'images/exercises/burpees.gif',
            'Mountain Climbers' => 'images/exercises/mountain_climbers.gif',
            'Russian Twists' => 'images/exercises/russian_twists.gif',
            'Gainage latéral (chaque côté)' => 'images/exercises/gainage_lateral.gif',
            // Ajoute d'autres exercices et leurs images correspondantes
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $prompt = sprintf(
                "Tu es un coach sportif. Crée un programme de gym personnalisé pour :
                - Sexe : %s
                - Âge : %s ans
                - Taille : %s cm
                - Poids : %s kg
                - Objectif : %s
                - Niveau : %s
                - Jours par semaine : %s
                - Équipement : %s
                Réponds au format HTML structuré avec des balises <table>, <tr>, <td> et inclue une balise <h2> pour chaque jour. Utilise des classes CSS simples (ex: 'day-title', 'exercise-table', 'exercise-name', 'exercise-sets', 'exercise-repetitions', 'exercise-rest', 'exercise-notes') pour que je puisse le styliser.
                ",
                $data['gender'],
                $data['age'],
                $data['height'],
                $data['weight'],
                $data['goal'],
                $data['level'],
                $data['daysPerWeek'],
                $data['equipment']
            );

            $geminiApiKey = $_ENV['GEMINI_API_KEY'];

            try {
                $httpClient = HttpClient::create();
                $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';

                $response = $httpClient->request('POST', $apiUrl, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'query' => [
                        'key' => $geminiApiKey,
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
                            'maxOutputTokens' => 2048,
                        ]
                    ],
                ]);

                $statusCode = $response->getStatusCode();

                if ($statusCode === 200) {
                    $responseData = $response->toArray();

                    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                        $program = $responseData['candidates'][0]['content']['parts'][0]['text'];
                    } else {
                        $program = 'Réponse reçue mais format HTML inattendu.';
                        error_log('Unexpected HTML format: ' . json_encode($responseData));
                    }
                } else {
                    $errorDetails = $response->getContent(false);
                    $program = 'Erreur lors de la communication avec Gemini: Code ' . $statusCode . '. Détails: ' . $errorDetails;
                    error_log('Gemini API Error: Status ' . $statusCode . ', Details: ' . $errorDetails);
                }
            } catch (\Exception $e) {
                $program = 'Erreur lors de la communication avec Gemini: ' . $e->getMessage();
                error_log('Gemini API Error Exception: ' . $e->getMessage());
            }
        }

        return $this->render('gymAi/index.html.twig', [
            'program' => $program,
            'images' => $images,
            'form' => $form->createView(),
        ]);
    }
}
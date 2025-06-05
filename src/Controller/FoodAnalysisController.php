<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FoodAnalysisController extends AbstractController
{
    private $geminiApiKey;
    
    public function __construct(ParameterBagInterface $params)
    {
        $this->geminiApiKey = $_ENV['GEMINI_API_KEY'] ?? '';
    }

    #[Route('/food-analysis', name: 'app_food_analysis')]
    public function index(Request $request): Response
    {
        $result = null;
        $error = null;
        
        if ($request->isMethod('POST')) {
            $file = $request->files->get('food_image');
            
            if ($file) {
                try {
                    // Lire l'image et l'encoder en base64
                    $imageData = file_get_contents($file->getPathname());
                    $base64Image = base64_encode($imageData);
                    
                    // Analyser l'image avec Gemini
                    $result = $this->analyzeImageWithGemini($base64Image);
                } catch (\Exception $e) {
                    $error = 'Une erreur est survenue lors de l\'analyse de l\'image: ' . $e->getMessage();
                }
            } else {
                $error = 'Veuillez télécharger une image d\'aliment.';
            }
        }
        
        return $this->render('nutrition/food_analysis.html.twig', [
            'result' => $result,
            'error' => $error
        ]);
    }
    
    private function analyzeImageWithGemini(string $base64Image): array
    {
        $client = HttpClient::create();
        
        $response = $client->request('POST', 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent', [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->geminiApiKey,
            ],
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Analyse cette image d'aliment. Identifie les aliments présents, estime le nombre de calories, et donne un score nutritionnel sur 10 (10 étant très sain). Réponds au format JSON avec les champs: 'foods' (liste des aliments), 'calories' (estimation), 'nutritionScore' (note sur 10), 'analysis' (courte analyse nutritionnelle), 'tips' (conseils pour améliorer)."
                            ],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $base64Image
                                ]
                            ]
                        ]
                    ]
                ],
                'generation_config' => [
                    'temperature' => 0.4,
                    'top_p' => 0.95,
                    'top_k' => 40,
                    'max_output_tokens' => 1024,
                ]
            ]
        ]);
        
        $data = $response->toArray();
        
        // Extraire la réponse JSON de Gemini
        $content = $data['candidates'][0]['content']['parts'][0]['text'];
        
        // Extraire le JSON de la réponse (au cas où il y aurait du texte avant/après)
        preg_match('/\{.*\}/s', $content, $matches);
        $jsonContent = $matches[0] ?? $content;
        
        // Décoder le JSON
        $result = json_decode($jsonContent, true);
        
        // Si le décodage échoue, créer un format par défaut
        if (!$result) {
            // Essayer d'extraire manuellement les informations
            preg_match('/calories.*?(\d+)/i', $content, $caloriesMatch);
            preg_match('/score.*?(\d+)/i', $content, $scoreMatch);
            
            $result = [
                'foods' => ['Non identifié'],
                'calories' => $caloriesMatch[1] ?? 'Non déterminé',
                'nutritionScore' => $scoreMatch[1] ?? 'Non déterminé',
                'analysis' => 'Analyse non disponible au format JSON',
                'tips' => 'Conseils non disponibles au format JSON',
                'rawResponse' => $content
            ];
        }
        
        return $result;
    }
}




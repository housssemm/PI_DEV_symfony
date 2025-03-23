<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeControlleController extends AbstractController{
    /**
     * @Route("/api/pose-data", name="pose_data", methods={"POST"})
     */
    public function handlePoseData(Request $request, PostureAnalysisService $analysisService): JsonResponse
    {
        // Récupération des données envoyées par le front-end
        $data = json_decode($request->getContent(), true);
        $poseData = $data['pose'];

        // Appeler le service d'analyse de posture pour traiter les données
        $result = $analysisService->analyzePose($poseData);

        // Retourner une réponse JSON avec le résultat de l'analyse
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Données de posture reçues et analysées',
            'result' => $result,
        ]);
    }
}

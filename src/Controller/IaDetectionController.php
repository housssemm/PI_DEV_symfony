<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class IaDetectionController extends AbstractController
{
    #[Route('/pose-detection', name: 'app_ia_detection')]
    public function index(): Response
    {
        return $this->render('ia_detection/index.html.twig');
    }

}

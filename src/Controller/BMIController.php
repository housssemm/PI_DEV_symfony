<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BMIController extends AbstractController
{
    #[Route('/bmi', name: 'app_bmi')]
    public function index(): Response
    {
        return $this->render('bmi/index.html.twig');
    }
} 
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClassesController extends AbstractController
{
    #[Route('/classes', name: 'app_classes')]
    public function index(): Response
    {
        return $this->render('classes/index.html.twig');
    }
} 
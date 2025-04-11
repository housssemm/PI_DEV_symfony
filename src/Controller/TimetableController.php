<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TimetableController extends AbstractController
{
    #[Route('/timetable', name: 'app_timetable')]
    public function index(): Response
    {
        return $this->render('timetable/index.html.twig');
    }
} 
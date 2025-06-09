<?php

namespace App\Controller;



use App\Repository\SeanceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class PlanningADRController extends AbstractController
{

    #[Route('/affichePlanAdr', name: 'app_afficher_planAdr')]
    public function index(): Response
    {
        return $this->render('planning/planningADR.html.twig', [
            'controller_name' => 'PlanningADRController',
        ]);
    }




}

<?php

namespace App\Controller;

use App\Repository\CoachRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/admin', name: 'app_home')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('base1.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/', name: 'app_homee')]
    public function index1(CoachRepository $coachRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $coachs = array_slice($coachRepository->findCoachsByCertificatValide(), 0, 5);

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'coachs' => $coachs,
        ]);
    }
    #[Route('/team', name: 'app_home_team')]
    public function index2(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/team.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/coachs', name: 'coach_list')]
    public function listAllCoachs(CoachRepository $coachRepository): Response
    {
        $coachs = $coachRepository->findCoachsByCertificatValide();
        return $this->render('home/listCoachs.html.twig', [
            'coachs' => $coachs,
        ]);
    }




} 
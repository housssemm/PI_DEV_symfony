<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Form\PlanningFormType;
use App\Repository\PlanningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class PlanningController extends AbstractController
{
    #[Route('/planning', name: 'app_planning')]
    public function add(Request $request, ManagerRegistry $doctrine, PlanningRepository $planningRepository): Response
    {
        $coachId = $request->query->get('coachId');

        if ($coachId !== null) {
            $existingPlanning = $planningRepository->findPlanningByCoach((int)$coachId);
        } else {
            $existingPlanning = null;
        }

        // Création d'un nouveau planning via le formulaire
        $newPlanning = new Planning();
        $form = $this->createForm(PlanningFormType::class, $newPlanning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coach = $newPlanning->getCoach();//recupere coach
            $em = $doctrine->getManager();
            $em->persist($newPlanning);
            $em->flush();

            $this->addFlash('success', 'Planning ajouté avec succès!');

            // Rediriger en ajoutant le coachId à l'URL pour pouvoir récupérer le planning existant
            return $this->redirectToRoute('app_planning', [
                'coachId' => $coach?->getId()
            ]);
        }

        return $this->render('planning/planningCoach.html.twig', [
            'form' => $form->createView(),
            'showModalOnLoad' => $form->isSubmitted() && !$form->isValid(),
            'Planning' => $existingPlanning,
        ]);
    }
    #[Route('/Delete/{id}', name: 'app_planning_delete')]
    public function delete($id,PlanningRepository $repoPlan,ManagerRegistry $doctrine ):response
    {
        $author=$repoPlan->find($id);
        $em=$doctrine->getManager();
        $em->remove($author);
        $em->flush();
        return $this->redirectToRoute('app_planning');
    }
    #[Route('/planning/update/{id}', name: 'app_planning_update')]
    public function update(int $id, Request $request, ManagerRegistry $doctrine, PlanningRepository $planningRepository): Response
    {
        $planning = $planningRepository->find($id);
        if (!$planning) {
            throw $this->createNotFoundException("Le planning n'existe pas !");
        }

        $form = $this->createForm(PlanningFormType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'Planning modifié avec succès!');
            return $this->redirectToRoute('app_planning');
        }

        return $this->render('planning/modifierPlanning.html.twig', [
            'form'     => $form->createView(),
            'planning' => $planning,
        ]);
    }




}

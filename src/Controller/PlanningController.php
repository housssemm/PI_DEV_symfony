<?php

namespace App\Controller;
use App\Entity\Coach;
use App\Entity\Planning;
use App\Entity\Seance;
use App\Form\PlanningFormType;
use App\Repository\PlanningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class PlanningController extends AbstractController
{
    #[Route('/affichePlan', name: 'app_afficher_plan', methods: ['GET', 'POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, Security $security, PlanningRepository $planningRepository): Response
    {
        $planning = new Planning();

        // Récupérer l'utilisateur connecté
        $loggedInUser = $security->getUser();

        if (!$loggedInUser || $loggedInUser->getDiscriminator() !== 'coach') {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que coach.');
        }

        // Associer le coach connecté au planning AVANT validation
        $coach = $doctrine->getRepository(Coach::class)->find($loggedInUser->getId());
        if (!$coach) {
            throw $this->createNotFoundException('Coach non trouvé.');
        }
        $planning->setCoach($coach);

        $form = $this->createForm(PlanningFormType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doctrine->getManager();
            $em->persist($planning);
            $em->flush();

            $this->addFlash('success', 'Planning ajouté avec succès!');
            return $this->redirectToRoute('app_afficher_plan');
        }

        $existingPlanning = $planningRepository->findPlanningByCoach($coach->getId());

        return $this->render('planning/planningCoach.html.twig', [
            'form' => $form->createView(),
            'showModalOnLoad' => $form->isSubmitted() && !$form->isValid(),
            'planning' => $existingPlanning,
        ]);
    }


        #[Route('/Delete/{id}', name: 'app_planning_delete')]
    public function delete($id,PlanningRepository $repoPlan,ManagerRegistry $doctrine ):response
    {
        $plan=$repoPlan->find($id);
        $em=$doctrine->getManager();
        $em->remove($plan);
        $em->flush();
        return $this->redirectToRoute('app_afficher_plan');
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
            return $this->redirectToRoute('app_afficher_plan');
        }

        return $this->render('planning/modifierPlanning.html.twig', [
            'form'     => $form->createView(),
            'planning' => $planning,
        ]);
    }








}

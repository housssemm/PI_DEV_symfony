<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Planning;
use App\Entity\Seance;
use App\Form\SeanceType;
use App\Repository\SeanceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class SeanceController extends AbstractController
{
    #[Route('/addSeance', name: 'addseance')]
    public function  add(ManagerRegistry $doctrine, Request $request, \Symfony\Component\Security\Core\Security $security ) : Response
    {
        $seance = new Seance();
        $loggedInUser = $security->getUser();

        if (!$loggedInUser || $loggedInUser->getDiscriminator() !== 'coach') {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que coach.');
        }

        // Récupérer le coach connecté
        $coach = $doctrine->getRepository(Coach::class)->find($loggedInUser->getId());
        if (!$coach) {
            throw $this->createNotFoundException('Coach non trouvé.');
        }

        // Associer le coach à la séance
        $seance->setCoach($coach);

        // Récupérer le planning de ce coach
        $planning = $doctrine->getRepository(Planning::class)->findPlanningByCoach($coach->getId());
        if (!$planning) {
            throw $this->createNotFoundException('Aucun planning trouvé pour ce coach.');
        }

        // Associer le planning à la séance
        $seance->setPlanning($planning);

        $form = $this->createForm(SeanceType::class, $seance);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() )
        {
            $em = $doctrine->getManager();
            $em->persist($seance);
            $em->flush();


            return $this->redirectToRoute('app_afficher_plan');
        }

        return $this->renderForm("planning/add_seance.html.twig",
            ["form"=>$form]) ;
    }
    #[Route('/DeleteSeance/{id}', name: 'app_seance_delete')]
    public function delete($id,SeanceRepository $repoSeance,ManagerRegistry $doctrine ):response
    {
        $seance=$repoSeance->find($id);
        $em=$doctrine->getManager();
        $em->remove($seance);
        $em->flush();
        return $this->redirectToRoute('app_afficher_plan');
    }

    #[Route('/seance/update/{id}', name: 'app_seance_update')]
    public function update(int $id, Request $request, ManagerRegistry $doctrine, SeanceRepository $seanceRepository): Response
    {
        $seance = $seanceRepository->find($id);
        if (!$seance) {
            throw $this->createNotFoundException("la séance n'existe pas !");
        }

        $form = $this->createForm(SeanceType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'séance modifié avec succès!');
            return $this->redirectToRoute('app_afficher_plan');
        }

        return $this->render('planning/modifierSeance.html.twig', [
            'form' => $form->createView(),
            'seance' => $seance,
        ]);

    }
    #[Route('/planning/day-json/{date}', name: 'day_json', methods: ['GET'])]
    public function getSeancesByDate(string $date, SeanceRepository $seanceRepository, LoggerInterface $logger): JsonResponse {
        try {
            // Validation stricte de la date
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->json(['error' => 'Format de date invalide'], Response::HTTP_BAD_REQUEST);
            }
            $dateObj = \DateTime::createFromFormat('!Y-m-d', $date, new \DateTimeZone('Africa/Tunis'));
            if (!$dateObj) {
                throw new \RuntimeException('Erreur de conversion de date');
            }

            $seances = $seanceRepository->findByDateCoach($dateObj);

            $formatted = array_map(function($seance) {
                return [
                    'titre' => $seance->getTitre() ?? 'Séance sans nom',
                    'date' => $seance->getDate()->format('d/m/Y'),
                    'heureDebut' => $seance->getHeureDebut()?->format('H:i') ?? '--:--',
                    'heureFin' => $seance->getHeureFin()?->format('H:i') ?? '--:--',
                    'type' => $seance->getType() ?? 'Non spécifié',
                    'coach' => $seance->getCoach()?->getNom() ?? 'Anonyme',
                    'adherent' => [
                        'nom' => $seance->getAdherent()->getNom() ?? 'Non attribué',
                        'prenom' => $seance->getAdherent()->getPrenom() ?? ''
                    ],
                    'deletePath' => $this->generateUrl('app_seance_delete', ['id' => $seance->getId()]),
                    'editPath' => $this->generateUrl('app_seance_update', ['id' => $seance->getId()])
                ];
            }, $seances);

            return $this->json($formatted);

        } catch (\Exception $e) {
            $logger->error("ERREUR : " . $e->getMessage());
            return $this->json(
                ['error' => 'Erreur technique'],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }
    #[Route('/AfficherSeance',name:'app_seance_admiin')]
    public function afficher(SeanceRepository $repoSeance):response
    {
        $list=$repoSeance->findAll();
        return $this->render('planning/seance_admin.html.twig',['seances'=>$list]);
    }
    #[Route('/deleteSeance/{id}', name: 'admin_seance_delete')]
    public function deleteSeanceAdmin($id,SeanceRepository $repoSeance,ManagerRegistry $doctrine ):response
    {
        $seance=$repoSeance->find($id);
        $em=$doctrine->getManager();
        $em->remove($seance);
        $em->flush();
        return $this->redirectToRoute('app_seance_admiin');
    }





}








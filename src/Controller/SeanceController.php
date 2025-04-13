<?php

namespace App\Controller;

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
    public function  add(ManagerRegistry $doctrine, Request  $request) : Response
    { $seance = new Seance() ;
        $form = $this->createForm(SeanceType::class, $seance);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() )
        {
            $em = $doctrine->getManager();
            $em->persist($seance);
            $em->flush();


            return $this->redirectToRoute('app_planning');
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
        return $this->redirectToRoute('app_planning');
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
            return $this->redirectToRoute('app_planning');
        }

        return $this->render('planning/modifierSeance.html.twig', [
            'form' => $form->createView(),
            'seance' => $seance,
        ]);

    }
    #[Route('/planning/day-json/{date}', name: 'day_json', methods: ['GET'])]
    public function getSeancesByDate(
        string $date,
        SeanceRepository $seanceRepository,
        LoggerInterface $logger
    ): JsonResponse {
        try {
            // Validation stricte de la date
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->json(['error' => 'Format de date invalide'], Response::HTTP_BAD_REQUEST);
            }

            $dateObj = \DateTime::createFromFormat('!Y-m-d', $date, new \DateTimeZone('Africa/Tunis'));
            if (!$dateObj) {
                throw new \RuntimeException('Erreur de conversion de date');
            }

            $seances = $seanceRepository->findByDate($dateObj);

            $formatted = array_map(function($seance) {
                return [
                    'titre' => $seance->getTitre() ?? 'Séance sans nom',
                    'date' => $seance->getDate()->format('d/m/Y'),
                    'heureDebut' => $seance->getHeureDebut()?->format('H:i') ?? '--:--',
                    'heureFin' => $seance->getHeureFin()?->format('H:i') ?? '--:--',
                    'type' => $seance->getType() ?? 'Non spécifié',
                    'coach' => $seance->getCoach()?->getUser()?->getNom() ?? 'Anonyme',
                    'adherent' => [
                        'nom' => $seance->getAdherent()?->getUser()?->getNom() ?? 'Non attribué',
                        'prenom' => $seance->getAdherent()?->getUser()?->getPrenom() ?? ''
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




}








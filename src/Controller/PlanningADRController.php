<?php

namespace App\Controller;



use App\Repository\SeanceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Core\Security;

class PlanningADRController extends AbstractController
{

    #[Route('/affichePlanAdr', name: 'app_afficher_planAdr')]
    public function index(): Response
    {
        return $this->render('planning/planningADR.html.twig', [
            'controller_name' => 'PlanningADRController',
        ]);
    }

    #[Route('/planningAdherent/day-json/{date}', name: 'day_json_Adherent', methods: ['GET'])]
    public function getSeancesAdrByDate(string $date, SeanceRepository $seanceRepository, LoggerInterface $logger, Security $security): JsonResponse
    {
        try {
            // Vérification du format de la date
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->json(['error' => 'Format de date invalide'], Response::HTTP_BAD_REQUEST);
            }

            // Conversion de la date
            $dateObj = \DateTime::createFromFormat('!Y-m-d', $date, new \DateTimeZone('Africa/Tunis'));
            if (!$dateObj) {
                throw new \RuntimeException('Erreur de conversion de date');
            }


            // Récupération de l'utilisateur connecté
            $user = $security->getUser();
            if (!$user || $user->getDiscriminator() !== 'adherent') {
                return $this->json(['error' => 'Non connecté en tant qu\'adhérent'], Response::HTTP_FORBIDDEN);
            }

            // Récupération des séances
            $seances = $seanceRepository->findByDateAndAdherent($dateObj, $user);
            $logger->info("Nombre de séances trouvées : " . count($seances));

            // Formatage des données à retourner
            $formatted = array_map(function ($seance) {
                return [
                    'id' => $seance->getId(),
                    'titre' => $seance->getTitre() ?? 'Séance sans nom',
                    'date' => $seance->getDate()->format('d/m/Y'),
                    'description' => $seance->getDescription() ?? '',
                    'heureDebut' => $seance->getHeureDebut()?->format('H:i') ?? '--:--',
                    'heureFin' => $seance->getHeureFin()?->format('H:i') ?? '--:--',
                    'type' => $seance->getType() ?? 'Non spécifié',
                    'coach' => $seance->getCoach()?->getNom() ?? 'Anonyme',

                ];
            }, $seances);

            return $this->json($formatted);

        } catch (\Exception $e) {
            $logger->error("ERREUR technique: " . $e->getMessage());
            return $this->json(['error' => 'Erreur technique'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}

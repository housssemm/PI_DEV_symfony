<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Planning;
use App\Entity\Seance;
use App\Form\SeanceType;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\AgoraDynamicKey\AccessToken2;
use App\AgoraDynamicKey\ServiceRtc;


class SeanceController extends AbstractController
{
    #[Route('/addSeance', name: 'addseance')]
    public function add(ManagerRegistry $doctrine, Request $request, Security $security, LoggerInterface $logger): Response
    {
        $seance = new Seance();
        $loggedInUser = $security->getUser();

        if (!$loggedInUser || $loggedInUser->getDiscriminator() !== 'coach') {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que coach.');
        }

        $coach = $doctrine->getRepository(Coach::class)->find($loggedInUser->getId());
        if (!$coach) {
            throw $this->createNotFoundException('Coach non trouvé.');
        }

        $seance->setCoach($coach);

        $planning = $doctrine->getRepository(Planning::class)->findPlanningByCoach($coach->getId());
        if (!$planning) {
            throw $this->createNotFoundException('Aucun planning trouvé pour ce coach.');
        }

        $seance->setPlanning($planning);

        $form = $this->createForm(SeanceType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager = $doctrine->getManager();

                if ($seance->getType() === 'EN_DIRECT') {
                    try {
                        $entityManager->persist($seance);
                        $entityManager->flush();

                        $lienVideo = $request->getSchemeAndHttpHost() . $this->generateUrl('seance_livestream', ['id' => $seance->getId()]);
                        $seance->setLienVideo($lienVideo);
                        $entityManager->flush();
                    } catch (\Exception $e) {
                        $logger->error('Erreur lors de la génération du lien', ['error' => $e->getMessage()]);
                        $this->addFlash('error', 'Erreur : ' . $e->getMessage());
                    }
                } else {
                    $videoFile = $form->get('VideoFile')->getData();
                    if ($videoFile && $seance->getType() === 'ENREGISTRE') {
                        $newFilename = uniqid() . '.' . $videoFile->guessExtension();
                        $videoFile->move(
                            $this->getParameter('videos_directory'),
                            $newFilename
                        );
                        $seance->setLienVideo('/uploads/videos/' . $newFilename);
                    }

                    $entityManager->persist($seance);
                    $entityManager->flush();
                }

                $this->addFlash('success', 'Séance ajoutée avec succès !');
                return $this->redirectToRoute('app_afficher_plan');
            } elseif ($seance->getType() === 'EN_DIRECT') {
                $entityManager = $doctrine->getManager();
                try {
                    $entityManager->persist($seance);
                    $entityManager->flush();

                    $lienVideo = $request->getSchemeAndHttpHost() . $this->generateUrl('seance_livestream', ['id' => $seance->getId()]);
                    $seance->setLienVideo($lienVideo);
                    $entityManager->flush();
                } catch (\Exception $e) {
                    $logger->error('Erreur lors de la génération temporaire', ['error' => $e->getMessage()]);
                    $this->addFlash('error', 'Erreur temporaire : ' . $e->getMessage());
                }

                $form = $this->createForm(SeanceType::class, $seance);
            }
        }

        return $this->renderForm('planning/add_seance.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/DeleteSeance/{id}', name: 'app_seance_delete')]
    public function delete($id, SeanceRepository $repoSeance, ManagerRegistry $doctrine): response
    {
        $seance = $repoSeance->find($id);
        $em = $doctrine->getManager();
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
            $videoFile = $form->get('VideoFile')->getData(); // ⚡ ici aussi : 'videoFile' minuscule

            if ($videoFile) {
                $newFilename = uniqid() . '.' . $videoFile->guessExtension();

                try {
                    $videoFile->move(
                        $this->getParameter('videos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Log ou message d'erreur
                }

                $seance->setLienVideo('/uploads/videos/' . $newFilename);
            }

            $doctrine->getManager()->flush();
            $this->addFlash('success', 'séance modifié avec succès!');
            return $this->redirectToRoute('app_afficher_plan');
        }

        return $this->render('planning/modifierSeance.html.twig', [
            'form' => $form->createView(),
            'seance' => $seance,
        ]);

    }

    #[Route('/AfficherSeance', name: 'app_seance_admiin')]
    public function afficher(SeanceRepository $repoSeance): response
    {
        $list = $repoSeance->findAll();
        return $this->render('planning/seance_admin.html.twig', ['seances' => $list]);
    }

    #[Route('/deleteSeance/{id}', name: 'admin_seance_delete')]
    public function deleteSeanceAdmin($id, SeanceRepository $repoSeance, ManagerRegistry $doctrine): response
    {
        $seance = $repoSeance->find($id);
        $em = $doctrine->getManager();
        $em->remove($seance);
        $em->flush();
        return $this->redirectToRoute('app_seance_admiin');
    }

    #[Route('/seance/video/{id<\d+>}', name: 'seance_video')]
    public function voirVideo(int $id, SeanceRepository $seanceRepository): Response
    {
        $seance = $seanceRepository->find($id);

        if (!$seance) {
            throw $this->createNotFoundException('Séance non trouvée');
        }

        return $this->render('planning/video.html.twig', [
            'seance' => $seance,
        ]);
    }

    #[Route('/planningAdherent/events-json', name: 'planningAdherent_events_json')]
    public function getEvents(EntityManagerInterface $entityManager, \Symfony\Component\Security\Core\Security $security): JsonResponse
    {
        $loggedInUser = $security->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Check if the user is a coach (adjust based on your user entity)
        if (!$this->isGranted('ROLE_ADHERENT')) { // Use roles instead of discriminator
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que adherent.');
        }

        // Assuming the user entity is the Coach entity itself
        $adherentId = $loggedInUser->getId();
        $seances = $entityManager->getRepository(Seance::class)->findByAdherentId($adherentId);

        $events = [];

        foreach ($seances as $seance) {
            $events[] = [
                'title' => $seance->getTitre(), // Only the session name, e.g., "Conference"
                'start' => $seance->getDate()->format('Y-m-d') . 'T' . $seance->getHeureDebut()->format('H:i:s'),
                'end' => $seance->getDate()->format('Y-m-d') . 'T' . $seance->getHeureFin()->format('H:i:s'),
                'allDay' => false,
                'color' => '#F58400',
                'extendedProps' => [
                    'description' => $seance->getDescription(),
                    'timeRange' => $seance->getHeureDebut()->format('H:i') . ' - ' . $seance->getHeureFin()->format('H:i'), // Add time range here
                ]
            ];
        }

        return $this->json($events);
    }

    #[Route('/planningCoach/events-json', name: 'planningCoach_events_json')]
    public function getCaochEvents(EntityManagerInterface $entityManager, \Symfony\Component\Security\Core\Security $security): JsonResponse
    {
        $loggedInUser = $security->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Check if the user is a coach (adjust based on your user entity)
        if (!$this->isGranted('ROLE_COACH')) { // Use roles instead of discriminator
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que coach.');
        }

        // Assuming the user entity is the Coach entity itself
        $coachId = $loggedInUser->getId();
        $seances = $entityManager->getRepository(Seance::class)->findByCoachId($coachId);

        $events = [];

        foreach ($seances as $seance) {
            $events[] = [
                'title' => $seance->getTitre(), // Only the session name, e.g., "Conference"
                'start' => $seance->getDate()->format('Y-m-d') . 'T' . $seance->getHeureDebut()->format('H:i:s'),
                'end' => $seance->getDate()->format('Y-m-d') . 'T' . $seance->getHeureFin()->format('H:i:s'),
                'allDay' => false,
                'color' => '#F58400',
                'extendedProps' => [
                    'description' => $seance->getDescription(),
                    'timeRange' => $seance->getHeureDebut()->format('H:i') . ' - ' . $seance->getHeureFin()->format('H:i'), // Add time range here
                ]
            ];
        }

        return $this->json($events);
    }

    #[Route('/planning/day-json/{date}', name: 'day_json', methods: ['GET'])]
    public function getSeancesByDate(string $date, SeanceRepository $seanceRepository, LoggerInterface $logger, \Symfony\Component\Security\Core\Security $security): JsonResponse
    {
        try {
            // Vérification du format de la date
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->json(['error' => 'Format de date invalide'], Response::HTTP_BAD_REQUEST);
            }

            // Conversion de la date en objet DateTime
            $dateObj = \DateTime::createFromFormat('!Y-m-d', $date, new \DateTimeZone('Africa/Tunis'));
            if (!$dateObj) {
                throw new \RuntimeException('Erreur de conversion de date');
            }

            // Récupération de l'utilisateur connecté
            $loggedInUser = $security->getUser();
            if (!$loggedInUser) {
                throw $this->createAccessDeniedException('Vous devez être connecté.');
            }

            // Vérifie si l'utilisateur est coach
            if (!$this->isGranted('ROLE_COACH')) {
                throw $this->createAccessDeniedException('Vous devez être connecté en tant que coach.');
            }

            // ID du coach connecté
            $coachId = $loggedInUser->getId();

            // Récupération des séances
            $seances = $seanceRepository->findByDateCoach($dateObj, $coachId);

            // Formatage des données pour la réponse JSON
            $formatted = array_map(function ($seance) {
                $adherent = $seance->getAdherent();
                return [
                    'id' => $seance->getId(),
                    'titre' => $seance->getTitre() ?? 'Séance sans nom',
                    'date' => $seance->getDate()?->format('d/m/Y'),
                    'heureDebut' => $seance->getHeureDebut()?->format('H:i') ?? '--:--',
                    'heureFin' => $seance->getHeureFin()?->format('H:i') ?? '--:--',
                    'type' => $seance->getType() ?? 'Non spécifié',
                    'coach' => $seance->getCoach()?->getNom() ?? 'Anonyme',
                    'adherent' => [
                        'nom' => $adherent ? ($adherent->getNom() ?? 'Non attribué') : 'Non attribué',
                        'prenom' => $adherent ? ($adherent->getPrenom() ?? '') : ''
                    ],
                    'lienVideo' => $seance->getLienVideo(),
                    'videoPath' => $this->generateUrl('seance_video', ['id' => $seance->getId()]),
                    'deletePath' => $this->generateUrl('app_seance_delete', ['id' => $seance->getId()]),
                    'editPath' => $this->generateUrl('app_seance_update', ['id' => $seance->getId()])
                ];
            }, $seances);

            return $this->json($formatted);

        } catch (\Exception $e) {
            $logger->error("ERREUR : " . $e->getMessage(), ['exception' => $e]);
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/planningAdherent/day-json/{date}', name: 'dayAdr_json', methods: ['GET'])]
    public function getSeancesAdrByDate(
        string                                    $date,
        SeanceRepository                          $seanceRepository,
        LoggerInterface                           $logger,
        \Symfony\Component\Security\Core\Security $security
    ): JsonResponse
    {
        try {
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->json(['error' => 'Format de date invalide'], Response::HTTP_BAD_REQUEST);
            }
            $dateObj = \DateTime::createFromFormat('!Y-m-d', $date, new \DateTimeZone('Africa/Tunis'));
            if (!$dateObj) {
                throw new \RuntimeException('Erreur de conversion de date');
            }

            // Get the logged-in user
            $loggedInUser = $security->getUser();
            if (!$loggedInUser) {
                throw $this->createAccessDeniedException('Vous devez être connecté.');
            }

            // Check if the user is a coach (adjust based on your user entity)
            if (!$this->isGranted('ROLE_ADHERENT')) { // Use roles instead of discriminator
                throw $this->createAccessDeniedException('Vous devez être connecté en tant que ADHERENT.');
            }


            $ADHERENTId = $loggedInUser->getId();

            $seances = $seanceRepository->findByDateAndAdherent($dateObj, $ADHERENTId);

            // Format the response
            $formatted = array_map(function ($seance) {
                $adherent = $seance->getAdherent();
                return [
                    'id' => $seance->getId(),
                    'titre' => $seance->getTitre() ?? 'Séance sans nom',
                    'date' => $seance->getDate()->format('d/m/Y'),
                    'heureDebut' => $seance->getHeureDebut()?->format('H:i') ?? '--:--',
                    'heureFin' => $seance->getHeureFin()?->format('H:i') ?? '--:--',
                    'type' => $seance->getType() ?? 'Non spécifié',
                    'coach' => $seance->getCoach()?->getNom() ?? 'Anonyme',
                    'description' => $seance->getDescription(),
                    'adherent' => [
                        'nom' => $adherent ? ($adherent->getNom() ?? 'Non attribué') : 'Non attribué',
                        'prenom' => $adherent ? ($adherent->getPrenom() ?? '') : ''
                    ],
                    'lienVideo' => $seance->getLienVideo(),
                    'videoPath' => $this->generateUrl('seance_video', ['id' => $seance->getId()]),
                ];
            }, $seances);

            return $this->json($formatted);

        } catch (\Exception $e) {
            $logger->error("ERREUR : " . $e->getMessage(), ['exception' => $e]);
            return $this->json(
                ['error' => $e->getMessage()], // Return the actual error for debugging
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }

    }

    #[Route('/seance/livestream/{id}', name: 'seance_livestream')]
    public function livestream(int $id, ManagerRegistry $doctrine, Security $security): Response
    {
        $seance = $doctrine->getRepository(Seance::class)->find($id);

        if (!$seance || $seance->getType() !== 'EN_DIRECT') {
            throw $this->createNotFoundException('Séance non trouvée ou non en direct.');
        }

        $loggedInUser = $security->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $appId = "9de2338612f847c3906a7e4a483c97fd";
        $appCertificate = "2482f9e016734bed878d0205b42fffca";
        $channelName = "seance_" . $seance->getId();

        $expireTimeInSeconds = 3600; // 1 heure

        // Définition du rôle en int : 1 = publisher, 2 = subscriber
        $role = $this->isGranted('ROLE_COACH') ? 1 : 2;
        $uid = (string)$loggedInUser->getId();

        $token = $this->generateAgoraToken($appId, $appCertificate, $channelName, $uid, $role, $expireTimeInSeconds);

        return $this->render('planning/live.html.twig', [
            'seance' => $seance,
            'appId' => $appId,
            'token' => $token,
            'channelName' => $channelName,
            'role' => $role,
        ]);
    }

    private function generateAgoraToken(string $appId, string $appCertificate, string $channelName, string $uid, int $role, int $expireInSeconds): string
    {
        try {
            $currentTs = (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp();
            $privilegeExpireTs = $currentTs + $expireInSeconds;
            \error_log("Token gen: appId=$appId, channel=$channelName, uid=$uid, role=$role, issuedAt=$currentTs, expireAt=$privilegeExpireTs");

            $token = new AccessToken2($appId, $appCertificate, $currentTs, $privilegeExpireTs);
            $serviceRtc = new ServiceRtc($channelName, $uid);
            $serviceRtc->addPrivilege(ServiceRtc::PRIVILEGE_JOIN_CHANNEL, $privilegeExpireTs);

            if ($role === 1) {
                $serviceRtc->addPrivilege(ServiceRtc::PRIVILEGE_PUBLISH_AUDIO_STREAM, $privilegeExpireTs);
                $serviceRtc->addPrivilege(ServiceRtc::PRIVILEGE_PUBLISH_VIDEO_STREAM, $privilegeExpireTs);
                $serviceRtc->addPrivilege(ServiceRtc::PRIVILEGE_PUBLISH_DATA_STREAM, $privilegeExpireTs);
            }

            $token->addService($serviceRtc);
            $builtToken = $token->build();
            \error_log("Generated token: $builtToken");
            return $builtToken;
        } catch (\Exception $e) {
            \error_log("Token gen failed: " . $e->getMessage());
            throw $e;
        }
    }
    #[Route('/seance/join/{id}', name: 'seance_join')]
    public function joinSeance(int $id, ManagerRegistry $doctrine, Security $security): Response
    {
        $seance = $doctrine->getRepository(Seance::class)->find($id);
        $loggedInUser = $security->getUser();

        if (
            !$seance ||
            $seance->getType() !== 'EN_DIRECT' ||
            !$loggedInUser ||
            !$this->isGranted('ROLE_ADHERENT')
        ) {
            throw $this->createAccessDeniedException('Accès non autorisé.');
        }

        if ($seance->getAdherent() && $seance->getAdherent()->getId() !== $loggedInUser->getId()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à rejoindre cette séance.');
        }

        $appId = "9de2338612f847c3906a7e4a483c97fd";
        $appCertificate = "2482f9e016734bed878d0205b42fffca";
        $channelName = "seance_" . $seance->getId();
        $expireTimeInSeconds = 3600;

        $role = 2; // adhérent = subscriber
        $uid = (string)$loggedInUser->getId();

        $token = null;
        try {
            $token = $this->generateAgoraToken($appId, $appCertificate, $channelName, $uid, $role, $expireTimeInSeconds);
            if (empty($token)) {
                throw new \Exception("Generated token is empty");
            }
        } catch (\Exception $e) {
            \error_log("Token generation failed in joinSeance: " . $e->getMessage());
            throw new \RuntimeException("Impossible de générer un token valide. Contactez l'administrateur.", 0, $e);
        }

        return $this->render('planning/liveAdr.html.twig', [
            'seance' => $seance,
            'appId' => $appId,
            'token' => $token,
            'channelName' => $channelName,
            'role' => $role,
        ]);
    }

}



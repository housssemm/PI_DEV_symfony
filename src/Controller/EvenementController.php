<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Evenement;
use App\Form\EvenementFormType;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
final class EvenementController extends AbstractController
{
    #[Route('/evenement', name: 'app_evenement')]
    public function index(): Response
    {
        return $this->render('evenement/index.html.twig', [
            'controller_name' => 'EvenementController',
        ]);
    }

    #[Route('/events', name: 'app_events')]
    public function listEvenement(EvenementRepository $evenementRepository,Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $loggedInUser = $security->getUser();
        $loggedInUser->getDiscriminator();
        $isCreateurEvenement = $loggedInUser instanceof \App\Entity\CreateurEvenement;

        $request = Request::createFromGlobals();
        $queryBuilder = $evenementRepository->createQueryBuilder('e');

        // Handle status filter
        $etatFilters = $request->query->all('etat');
        if (!empty($etatFilters)) {
            $queryBuilder->andWhere('e.etat IN (:etats)')
                ->setParameter('etats', $etatFilters);
        }

        // Handle type filter
        $typeFilters = $request->query->all('type');
        if (!empty($typeFilters)) {
            $queryBuilder->andWhere('e.type IN (:types)')
                ->setParameter('types', $typeFilters);
        }

        // Handle price filter
        $prixFilters = $request->query->all('prix');
        if (!empty($prixFilters)) {
            $priceConditions = [];
            foreach ($prixFilters as $filter) {
                switch ($filter) {
                    case '0-50':
                        $priceConditions[] = 'e.prix <= 50';
                        break;
                    case '50-100':
                        $priceConditions[] = 'e.prix > 50 AND e.prix <= 100';
                        break;
                    case '100+':
                        $priceConditions[] = 'e.prix > 100';
                        break;
                }
            }
            if (!empty($priceConditions)) {
                $queryBuilder->andWhere('(' . implode(' OR ', $priceConditions) . ')');
            }
        }

        // Order by date
        $queryBuilder->orderBy('e.dateDebut', 'DESC');

        $list = $queryBuilder->getQuery()->getResult();

        // Convert images to base64 for display
        foreach ($list as $event) {
            $image = $event->getImage();
            if ($image) {
                $event->setBase64Image(base64_encode($image));
            }
        }

        return $this->render('evenement/ListEvenement.html.twig', [
            'list' => $list,

            'isCreateurEvenement' => $isCreateurEvenement,
        ]);
    }


    #[Route('/events/admin', name: 'app_events_admiin')]
    public function listEvenementAdmin(EvenementRepository $evenementRepository): Response
    {
        $request = Request::createFromGlobals();
        $queryBuilder = $evenementRepository->createQueryBuilder('e');

        // Handle status filter
        $etatFilters = $request->query->all('etat');
        if (!empty($etatFilters)) {
            $queryBuilder->andWhere('e.etat IN (:etats)')
                ->setParameter('etats', $etatFilters);
        }

        // Handle type filter
        $typeFilters = $request->query->all('type');
        if (!empty($typeFilters)) {
            $queryBuilder->andWhere('e.type IN (:types)')
                ->setParameter('types', $typeFilters);
        }

        // Handle price filter
        $prixFilters = $request->query->all('prix');
        if (!empty($prixFilters)) {
            $priceConditions = [];
            foreach ($prixFilters as $filter) {
                switch ($filter) {
                    case '0-50':
                        $priceConditions[] = 'e.prix <= 50';
                        break;
                    case '50-100':
                        $priceConditions[] = 'e.prix > 50 AND e.prix <= 100';
                        break;
                    case '100+':
                        $priceConditions[] = 'e.prix > 100';
                        break;
                }
            }
            if (!empty($priceConditions)) {
                $queryBuilder->andWhere('(' . implode(' OR ', $priceConditions) . ')');
            }
        }

        // Order by date
        $queryBuilder->orderBy('e.dateDebut', 'DESC');

        $list = $queryBuilder->getQuery()->getResult();

        // Convert images to base64 for display
        foreach ($list as $event) {
            $image = $event->getImage();
            if ($image) {
                $event->setBase64Image(base64_encode($image));
            }
        }

        return $this->render('evenement/admin.html.twig', [
            'list' => $list,
        ]);
    }




    #[Route('/events/map', name: 'app_nearby_events')]
    public function map(EntityManagerInterface $em, HttpClientInterface $httpClient): Response
    {
        $events = $em->getRepository(Evenement::class)->findAll();
        $eventsArray = [];
        foreach ($events as $event) {
            $lieu = $event->getLieu();
            $response = $httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $lieu,
                    'format' => 'json',
                    'limit' => 1
                ],
                'headers' => [
                    'User-Agent' => 'CoachiniMap/1.0'
                ]
            ]);
            $data = $response->toArray();
            if (!empty($data)) {
                $latitude = $data[0]['lat'];
                $longitude = $data[0]['lon'];
                $base64Image = null;
                $image = $event->getImage();
                if ($image) {
                    $base64Image = base64_encode($image);
                    $event->setBase64Image($base64Image);
                }
                $eventsArray[] = [
                    'id' => $event->getId(),
                    'titre' => $event->getTitre(),
                    'dateDebut' => $event->getDateDebut()->format('Y-m-d'),
                    'lieu' => $lieu,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'base64Image' => $base64Image
                ];
            }
        }
        return $this->render('evenement/EventMapDisplay.html.twig', [
            'events' => $eventsArray
        ]);
    }



    #[Route('/events/update/{id}', name: 'app_update_event')]
    public function update(Request $request, EvenementRepository $ev, EntityManagerInterface $em, $id): Response
    {
        $event = $ev->find($id);

        if (!$event) {
            $this->addFlash('error', 'Événement non trouvé.');
            return $this->redirectToRoute('app_events');
        }

        // Convert image to base64 for display
        $image = $event->getImage();
        if ($image) {
            $event->setBase64Image(base64_encode($image));
        }

        if ($request->isMethod('POST')) {
            $errors = [];

            // Validate inputs
            if (empty($request->request->get('titre'))) {
                $errors['titre'] = 'Le titre est requis';
            }

            if (empty($request->request->get('description'))) {
                $errors['description'] = 'La description est requise';
            }

            $dateDebut = $request->request->get('dateDebut');
            $dateFin = $request->request->get('dateFin');

            if (empty($dateDebut)) {
                $errors['dateDebut'] = 'La date de début est requise';
            }

            if (empty($dateFin)) {
                $errors['dateFin'] = 'La date de fin est requise';
            } elseif ($dateDebut && $dateFin && new \DateTime($dateDebut) > new \DateTime($dateFin)) {
                $errors['dateFin'] = 'La date de fin doit être après la date de début';
            }

            if (empty($request->request->get('lieu'))) {
                $errors['lieu'] = 'Le lieu est requis';
            }

            if (!is_numeric($request->request->get('prix')) || $request->request->get('prix') < 0) {
                $errors['prix'] = 'Le prix doit être un nombre positif';
            }

            if (!is_numeric($request->request->get('capaciteMaximale')) || $request->request->get('capaciteMaximale') <= 0) {
                $errors['capaciteMaximale'] = 'La capacité doit être un nombre positif';
            }

            // Handle image upload if provided
            if ($request->files->has('image')) {
                $imageFile = $request->files->get('image');
                if ($imageFile) {
                    if ($imageFile->getSize() > 5 * 1024 * 1024) {
                        $errors['image'] = 'La taille de l\'image ne peut pas dépasser 5MB';
                    }

                    $mimeType = $imageFile->getMimeType();
                    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                        $errors['image'] = 'Seuls les formats JPG et PNG sont acceptés';
                    }
                }
            }

            if (count($errors) > 0) {
                return $this->render('evenement/UpdateEvenement.html.twig', [
                    'event' => $event,
                    'errors' => $errors
                ]);
            }

            // Update event if no errors
            $event->setTitre($request->request->get('titre'));
            $event->setDescription($request->request->get('description'));
            $event->setDateDebut(new \DateTime($dateDebut));
            $event->setDateFin(new \DateTime($dateFin));
            $event->setLieu($request->request->get('lieu'));
            $event->setEtat($request->request->get('etat'));
            $event->setPrix($request->request->get('prix'));
            $event->setType($request->request->get('type'));
            $event->setOrganisateur($request->request->get('organisateur'));
            $event->setCapaciteMaximale($request->request->get('capaciteMaximale'));

            // Update image only if a new one was uploaded
            if ($request->files->has('image') && $imageFile) {
                $imageContent = file_get_contents($imageFile->getPathname());
                $event->setImage($imageContent);
            }

            $em->flush();
            $this->addFlash('success', 'L\'événement a été mis à jour avec succès.');
            return $this->redirectToRoute('app_events');
        }

        return $this->render('evenement/UpdateEvenement.html.twig', [
            'event' => $event,
            'errors' => []
        ]);
    }



    #[Route('/events/delete/{id}', name: 'app_delete')]
    public function delete(EvenementRepository $ev, EntityManagerInterface $em, $id): RedirectResponse
    {
        // Find the event by its ID
        $event = $ev->find($id);

        if ($event) {
            // Check if there are any participants
            $participants = $event->getParticipantevenements();

            if ($participants->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer cet événement car il y a des participants inscrits.');
                return $this->redirectToRoute('app_events');
            }

            // If no participants, proceed with deletion
            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'L\'événement a été supprimé avec succès.');
            return $this->redirectToRoute('app_events');
        }

        // If the event is not found, redirect back to the events list with an error message
        $this->addFlash('error', 'Événement non trouvé.');
        return $this->redirectToRoute('app_events');
    }


    #[Route('/events/add', name: 'app_add_event')]
    public function add(Request $request, EntityManagerInterface $em ): Response
    {


        $event = new Evenement();
        $event->setEtat('ACTIF'); // Set the etat to 'ACTIF' by default

        // Crée le formulaire basé sur le type EvenementFormType
        $form = $this->createForm(EvenementFormType::class, $event);

        // Gère la requête (hydrate l'objet + vérifie s'il y a soumission)
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Gère l'image uploadée
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageContent = file_get_contents($imageFile->getPathname());
                $event->setImage($imageContent);
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'L\'événement a été ajouté avec succès.');
            return $this->redirectToRoute('app_events');
        }

        // Rend la vue avec le formulaire
        return $this->render('evenement/AddEvenement.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_details')]
    public function eventDetails(Evenement $event,Security $security): Response
    {
        $loggedInUser = $security->getUser();
        $loggedInUser->getDiscriminator();
        $isCreateurEvenement = $loggedInUser instanceof \App\Entity\CreateurEvenement;

        // Convert image to base64 for display
        $image = $event->getImage();
        if ($image) {
            $event->setBase64Image(base64_encode($image));
        }

        return $this->render('evenement/EventDetails.html.twig', [
            'event' => $event,
            'isCreateurEvenement' => $isCreateurEvenement,
        ]);
    }

    public function ibm(Request $request)
    {
        $bmi = null;
        $message = null;

        if ($request->isMethod('POST')) {
            // Get values from the form
            $height = $request->request->get('height');
            $weight = $request->request->get('weight');
            $age = $request->request->get('age');
            $sex = $request->request->get('sex');

            // Validate the form data (you can add more validation here)
            if ($height && $weight) {
                // Convert height from cm to meters
                $heightInMeters = $height / 100;

                // Calculate BMI (BMI = weight / height^2)
                $bmi = $weight / ($heightInMeters * $heightInMeters);

                // Create a message based on BMI
                if ($bmi < 18.5) {
                    $message = 'Underweight';
                } elseif ($bmi >= 18.5 && $bmi <= 24.9) {
                    $message = 'Healthy weight';
                } elseif ($bmi >= 25 && $bmi <= 29.9) {
                    $message = 'Overweight';
                } else {
                    $message = 'Obese';
                }
            } else {
                $message = 'Please provide valid height and weight values.';
            }
        }

        // Render the form view with results
        return $this->render('evenement/bmi-calculator.html.twig', [
            'bmi' => $bmi,
            'message' => $message
        ]);
    }
    #[Route('/bmii', name: 'bmi_calculator')]
    public function calculateBmi(Request $request)
    {
        $height = $request->get('height');
        $weight = $request->get('weight');

        if ($height && $weight) {
            $bmi = $weight / (($height / 100) ** 2); // Calculate BMI
            $bmiCategory = $this->getBmiCategory($bmi);
        } else {
            $bmi = null;
            $bmiCategory = null;
        }

        return $this->render('evenement/bmi-calculator.html.twig', [
            'bmi' => $bmi,
            'bmiCategory' => $bmiCategory, // Pass bmiCategory to Twig template
        ]);
    }

    private function getBmiCategory($bmi)
    {
        if ($bmi < 18.5) return "Underweight";
        if ($bmi >= 18.5 && $bmi < 24.9) return "Healthy";
        if ($bmi >= 25 && $bmi < 29.9) return "Overweight";
        return "Obese";
    }


}



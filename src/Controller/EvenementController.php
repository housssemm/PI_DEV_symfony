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
    public function listEvenement(EvenementRepository $evenementRepository): Response
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

        return $this->render('evenement/ListEvenement.html.twig', [
            'list' => $list,
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
            $event->setTitre($request->request->get('titre'));
            $event->setDescription($request->request->get('description'));
            $event->setDateDebut(new \DateTime($request->request->get('dateDebut')));
            $event->setDateFin(new \DateTime($request->request->get('dateFin')));
            $event->setLieu($request->request->get('lieu'));
            $event->setEtat($request->request->get('etat'));
            $event->setPrix($request->request->get('prix'));
            $event->setType($request->request->get('type'));
            $event->setOrganisateur($request->request->get('organisateur'));
            $event->setCapaciteMaximale($request->request->get('capaciteMaximale'));

            // Handle image upload if provided
            if ($request->files->has('image')) {
                $imageFile = $request->files->get('image');
                if ($imageFile) {
                    $imageContent = file_get_contents($imageFile->getPathname());
                    $event->setImage($imageContent);
                }
            }

            $em->flush();
            $this->addFlash('success', 'L\'événement a été mis à jour avec succès.');
            return $this->redirectToRoute('app_events');
        }

        return $this->render('evenement/UpdateEvenement.html.twig', [
            'event' => $event
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
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $event = new Evenement();

            $event->setTitre($request->request->get('titre'));
            $event->setDescription($request->request->get('description'));
            $event->setDateDebut(new \DateTime($request->request->get('dateDebut')));
            $event->setDateFin(new \DateTime($request->request->get('dateFin')));
            $event->setLieu($request->request->get('lieu'));
            $event->setEtat($request->request->get('etat'));
            $event->setPrix($request->request->get('prix'));
            $event->setType($request->request->get('type'));
            $event->setOrganisateur($request->request->get('organisateur'));
            $event->setCapaciteMaximale($request->request->get('capaciteMaximale'));

            // Handle image upload if provided
            if ($request->files->has('image')) {
                $imageFile = $request->files->get('image');
                if ($imageFile) {
                    $imageContent = file_get_contents($imageFile->getPathname());
                    $event->setImage($imageContent);
                }
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'L\'événement a été ajouté avec succès.');
            return $this->redirectToRoute('app_events');
        }

        return $this->render('evenement/AddEvenement.html.twig');
    }

    #[Route('/event/{id}', name: 'app_event_details')]
    public function eventDetails(Evenement $event): Response
    {
        // Convert image to base64 for display
        $image = $event->getImage();
        if ($image) {
            $event->setBase64Image(base64_encode($image));
        }

        return $this->render('evenement/EventDetails.html.twig', [
            'event' => $event
        ]);
    }
}

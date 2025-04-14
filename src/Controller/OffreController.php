<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Form\OffreType;
use App\Repository\OffreRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/offre')]
final class OffreController extends AbstractController
{
    #[Route('/', name: 'offre_index')]
    public function index(Request $request, OffreRepository $offreRepository): Response
    {
        $nom = $request->query->get('nom');
        $date = $request->query->get('date');

        $offres = $offreRepository->findByFilters($nom, $date);

        // Création du tableau d’événements pour FullCalendar
        $offresJson = [];

        foreach ($offres as $offre) {
            $offresJson[] = [
                'title' => $offre->getNom(),
                'start' => $offre->getDureeValidite()->format('Y-m-d'),
                'end' => $offre->getDureeValidite()->format('Y-m-d'),
                'color' => $offre->getEtat() === 'ACTIVE' ? '#22c55e' : '#ef4444'
            ];
        }

        return $this->render('offre/index.html.twig', [
            'offres' => $offres,
            'offres_json' => json_encode($offresJson), // ← on passe ce tableau au Twig
        ]);
    }



    #[Route('/list', name: 'offre_list')]
    public function list(Request $request, OffreRepository $offreRepository): Response
    {
        $nom = $request->query->get('nom');
        $date = $request->query->get('date');
        $tri = $request->query->get('tri');

        // Récupérer les offres en appliquant les filtres et le tri
        $offres = $offreRepository->findByFilters($nom, $date, $tri);

        // Création du tableau d’événements pour FullCalendar
        $offresJson = [];
        foreach ($offres as $offre) {
            $offresJson[] = [
                'title' => $offre->getNom(),
                'start' => $offre->getDureeValidite()->format('Y-m-d'),
                'end' => $offre->getDureeValidite()->format('Y-m-d'),
                'color' => $offre->getEtat() === 'ACTIVE' ? '#22c55e' : '#ef4444',
            ];
        }

        return $this->render('offre/ListOffre.html.twig', [
            'offres' => $offres,
            'offres_json' => json_encode($offresJson), // Passer les événements à Twig
        ]);
    }






    #[Route('/new', name: 'offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offre = new Offre();
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($offre->getOffrecoachs() as $offreCoach) {
                $offreCoach->setOffre($offre);
            }
            foreach ($offre->getOffreproduits() as $offreProduit) {
                $offreProduit->setOffre($offre);
            }

            $em->persist($offre);
            $em->flush();

            return $this->redirectToRoute('offre_list');
        }

        return $this->render('offre/AddOffre.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new/produit', name: 'offre_new_produit', methods: ['GET', 'POST'])]
    public function newProduit(Request $request, EntityManagerInterface $em): Response
    {
        $offre = new Offre();
        $form = $this->createForm(OffreType::class, $offre, [
            'exclude_coachs' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($offre->getOffreproduits() as $offreProduit) {
                $offreProduit->setOffre($offre);
            }

            $em->persist($offre);
            $em->flush();

            return $this->redirectToRoute('offre_list');
        }

        return $this->render('offre/AddOffreProduit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new/coach', name: 'offre_new_coach', methods: ['GET', 'POST'])]
    public function newCoach(Request $request, EntityManagerInterface $em): Response
    {
        $offre = new Offre();
        $form = $this->createForm(OffreType::class, $offre, [
            'exclude_produits' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($offre->getOffrecoachs() as $offreCoach) {
                $offreCoach->setOffre($offre);
            }

            $em->persist($offre);
            $em->flush();

            return $this->redirectToRoute('offre_list');
        }

        return $this->render('offre/AddOffreCoach.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'offre_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        return $this->render('offre/OffreDetails.html.twig', [
            'offre' => $offre
        ]);
    }

    #[Route('/{id}/edit', name: 'offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('offre_list');
        }

        return $this->render('offre/UpdateOffre.html.twig', [
            'offre' => $offre,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'offre_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
            $em->remove($offre);
            $em->flush();
        }

        return $this->redirectToRoute('offre_list');
    }

    #[Route('/offre/admin', name: 'offre_admin_list')]
    public function listOffresAdmin(OffreRepository $offreRepository): Response
    {
        $offres = $offreRepository->findAll(); // ou applique des filtres selon tes besoins

        return $this->render('offre/ListOffreAdmin.html.twig', [
            'offres' => $offres,
            'offres_json' => json_encode(array_map(function ($offre) {
                return [
                    'title' => $offre->getNom(),
                    'start' => $offre->getDureeValidite()->format('Y-m-d'),
                ];
            }, $offres)),
        ]);
    }



}
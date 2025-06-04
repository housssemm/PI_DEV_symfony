<?php

namespace App\Controller;

use App\Entity\Plats;
use App\Form\PlatsType; // Utilisez la classe que vous avez décidé de garder
// OU
// use App\Form\PlatsFormType; // Si vous avez renommé la classe

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/plats')]
class PlatsController extends AbstractController
{
    #[Route('/', name: 'app_plats_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $plats = $entityManager->getRepository(Plats::class)->findAll();
        
        return $this->render('plats/index.html.twig', [
            'plats' => $plats,
        ]);
    }

    #[Route('/new', name: 'app_plats_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $plat = new Plats();
        $form = $this->createForm(PlatsType::class, $plat); // Utilisez la classe que vous avez décidé de garder
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement du fichier image si nécessaire
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // Traiter l'upload du fichier image
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                // Vérifier si le répertoire existe, sinon le créer
                $imagesDirectory = $this->getParameter('images_directory');
                if (!file_exists($imagesDirectory)) {
                    mkdir($imagesDirectory, 0777, true);
                }

                // Déplace le fichier dans le répertoire de stockage
                $imageFile->move(
                    $imagesDirectory,
                    $newFilename
                );

                // Sauvegarder le chemin de l'image dans l'entité
                $plat->setImage('/uploads/images/' . $newFilename);
            }

            // Enregistrement dans la base de données
            $entityManager->persist($plat);
            $entityManager->flush();

            $this->addFlash('success', 'Le plat a été créé avec succès.');
            return $this->redirectToRoute('app_plats_index'); // Rediriger vers la liste des plats
        }

        return $this->render('plats/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'app_plats_show', methods: ['GET'])]
    public function show(Plats $plat): Response
    {
        return $this->render('plats/show.html.twig', [
            'plat' => $plat,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_plats_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Plats $plat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlatsType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement du fichier image si nécessaire
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // Traiter l'upload du fichier image
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                
                // Sauvegarder le chemin de l'image dans l'entité
                $plat->setImage('/uploads/images/' . $newFilename);
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Le plat a été modifié avec succès.');
            return $this->redirectToRoute('app_plats_index');
        }

        return $this->render('plats/edit.html.twig', [
            'plat' => $plat,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'app_plats_delete', methods: ['POST'])]
    public function delete(Request $request, Plats $plat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($plat);
            $entityManager->flush();
            $this->addFlash('success', 'Le plat a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_plats_index');
    }
}





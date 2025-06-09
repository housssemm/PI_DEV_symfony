<?php

namespace App\Controller;

use App\Entity\ProgressPost;
use App\Form\ProgressPostType;
use App\Repository\ProgressPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/progress')]
class ProgressController extends AbstractController
{
    #[Route('/', name: 'app_progress_index', methods: ['GET'])]
    public function index(ProgressPostRepository $progressPostRepository): Response
    {
        $progressPosts = $progressPostRepository->findAllPublic();

        return $this->render('progress/index.html.twig', [
            'progress_posts' => $progressPosts,
        ]);
    }

    #[Route('/new', name: 'app_progress_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $progressPost = new ProgressPost();
        $progressPost->setUser($this->getUser());

        $form = $this->createForm(ProgressPostType::class, $progressPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle before image upload
            $beforeImage = $form->get('beforeImage')->getData();
            if ($beforeImage) {
                $originalFilename = pathinfo($beforeImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $beforeImage->guessExtension();
                $beforeImage->move(
                    $this->getParameter('progress_uploads_directory'),
                    $newFilename
                );
                $progressPost->setBeforeImage($newFilename);
            }

            // Handle after image upload
            $afterImage = $form->get('afterImage')->getData();
            if ($afterImage) {
                $originalFilename = pathinfo($afterImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $afterImage->guessExtension();
                $afterImage->move(
                    $this->getParameter('progress_uploads_directory'),
                    $newFilename
                );
                $progressPost->setAfterImage($newFilename);
            }

            $entityManager->persist($progressPost);
            $entityManager->flush();

            $this->addFlash('success', 'Votre progression a été partagée avec succès !');
            return $this->redirectToRoute('app_progress_index');
        }

        return $this->render('progress/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_progress_show', methods: ['GET'])]
    public function show(ProgressPost $progressPost): Response
    {
        return $this->render('progress/show.html.twig', [
            'progress_post' => $progressPost,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_progress_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, ProgressPost $progressPost, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if ($this->getUser() !== $progressPost->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette progression.');
        }

        $form = $this->createForm(ProgressPostType::class, $progressPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle before image upload
            $beforeImage = $form->get('beforeImage')->getData();
            if ($beforeImage) {
                // Delete old image if exists
                if ($progressPost->getBeforeImage()) {
                    $oldImage = $this->getParameter('progress_uploads_directory') . '/' . $progressPost->getBeforeImage();
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }

                $originalFilename = pathinfo($beforeImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $beforeImage->guessExtension();
                $beforeImage->move(
                    $this->getParameter('progress_uploads_directory'),
                    $newFilename
                );
                $progressPost->setBeforeImage($newFilename);
            }

            // Handle after image upload
            $afterImage = $form->get('afterImage')->getData();
            if ($afterImage) {
                // Delete old image if exists
                if ($progressPost->getAfterImage()) {
                    $oldImage = $this->getParameter('progress_uploads_directory') . '/' . $progressPost->getAfterImage();
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }

                $originalFilename = pathinfo($afterImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $afterImage->guessExtension();
                $afterImage->move(
                    $this->getParameter('progress_uploads_directory'),
                    $newFilename
                );
                $progressPost->setAfterImage($newFilename);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre progression a été mise à jour avec succès !');
            return $this->redirectToRoute('app_progress_show', ['id' => $progressPost->getId()]);
        }

        return $this->render('progress/edit.html.twig', [
            'form' => $form,
            'progress_post' => $progressPost,
        ]);
    }

    #[Route('/{id}', name: 'app_progress_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, ProgressPost $progressPost, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $progressPost->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette progression.');
        }

        if ($this->isCsrfTokenValid('delete' . $progressPost->getId(), $request->request->get('_token'))) {
            // Delete images if they exist
            if ($progressPost->getBeforeImage()) {
                $beforeImage = $this->getParameter('progress_uploads_directory') . '/' . $progressPost->getBeforeImage();
                if (file_exists($beforeImage)) {
                    unlink($beforeImage);
                }
            }
            if ($progressPost->getAfterImage()) {
                $afterImage = $this->getParameter('progress_uploads_directory') . '/' . $progressPost->getAfterImage();
                if (file_exists($afterImage)) {
                    unlink($afterImage);
                }
            }

            $entityManager->remove($progressPost);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Votre progression a été supprimée avec succès !');
        return $this->redirectToRoute('app_progress_index');
    }

    #[Route('/{id}/like', name: 'app_progress_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function like(ProgressPost $progressPost, EntityManagerInterface $entityManager): Response
    {
        $progressPost->setLikes($progressPost->getLikes() + 1);
        $entityManager->flush();

        return $this->json([
            'likes' => $progressPost->getLikes()
        ]);
    }
}

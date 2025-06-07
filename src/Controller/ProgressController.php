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

#[Route('/progress')]
class ProgressController extends AbstractController
{
    #[Route('/', name: 'app_progress_index', methods: ['GET'])]
    public function index(ProgressPostRepository $progressPostRepository): Response
    {
        return $this->render('progress/index.html.twig', [
            'progress_posts' => $progressPostRepository->findLatestPublicPosts(),
        ]);
    }

    #[Route('/new', name: 'app_progress_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $progressPost = new ProgressPost();
        $progressPost->setUser($this->getUser());

        $form = $this->createForm(ProgressPostType::class, $progressPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($progressPost);
            $entityManager->flush();

            $this->addFlash('success', 'Votre progression a été partagée avec succès!');
            return $this->redirectToRoute('app_progress_index');
        }

        return $this->render('progress/new.html.twig', [
            'progress_post' => $progressPost,
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
    public function edit(Request $request, ProgressPost $progressPost, EntityManagerInterface $entityManager): Response
    {
        if ($progressPost->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette publication.');
        }

        $form = $this->createForm(ProgressPostType::class, $progressPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre progression a été mise à jour avec succès!');
            return $this->redirectToRoute('app_progress_index');
        }

        return $this->render('progress/edit.html.twig', [
            'progress_post' => $progressPost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_progress_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, ProgressPost $progressPost, EntityManagerInterface $entityManager): Response
    {
        if ($progressPost->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette publication.');
        }

        if ($this->isCsrfTokenValid('delete' . $progressPost->getId(), $request->request->get('_token'))) {
            $entityManager->remove($progressPost);
            $entityManager->flush();
            $this->addFlash('success', 'Votre progression a été supprimée avec succès!');
        }

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

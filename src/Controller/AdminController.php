<?php
////
////namespace App\Controller;
////
////use App\Entity\User;
////use Doctrine\ORM\EntityManagerInterface;
////use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
////use Symfony\Component\HttpFoundation\Response;
////use Symfony\Component\Routing\Annotation\Route;
////use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
////
////class AdminController extends AbstractController
////{
////    #[Route('/admin', name: 'admin_dashboard')]
////    #[Route('/admin/requests', name: 'admin_requests')]
////    public function showRequests(EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
////    {
////        $coachRepo = $em->getRepository('App\Entity\Coach');
////        $investisseurRepo = $em->getRepository('App\Entity\InvestisseurProduit');
////        $createurRepo = $em->getRepository('App\Entity\CreateurEvenement');
////
////        $coachs = $coachRepo->findBy(['Certificat_valide' => false]);
////        $investisseurs = $investisseurRepo->findBy(['certificatValide' => false]);
////        $createurs = $createurRepo->findBy(['certificatValide' => false]);
////        $users = array_merge($coachs, $investisseurs, $createurs);
////
////        $adherentCount = $em->createQueryBuilder()
////            ->select('COUNT(u.id)')
////            ->from('App\Entity\Adherent', 'u')
////            ->getQuery()
////            ->getSingleScalarResult();
////
////        $coachCount = $em->createQueryBuilder()
////            ->select('COUNT(u.id)')
////            ->from('App\Entity\Coach', 'u')
////            ->where('u.Certificat_valide = :certificatValide')
////            ->setParameter('certificatValide', true)
////            ->getQuery()
////            ->getSingleScalarResult();
////
////        $createurCount = $em->createQueryBuilder()
////            ->select('COUNT(u.id)')
////            ->from('App\Entity\CreateurEvenement', 'u')
////            ->where('u.certificatValide = :certificatValide')
////            ->setParameter('certificatValide', true)
////            ->getQuery()
////            ->getSingleScalarResult();
////
////        $investisseurCount = $em->createQueryBuilder()
////            ->select('COUNT(u.id)')
////            ->from('App\Entity\InvestisseurProduit', 'u')
////            ->where('u.certificatValide = :certificatValide')
////            ->setParameter('certificatValide', true)
////            ->getQuery()
////            ->getSingleScalarResult();
////
////        $totalUsers = $adherentCount + $coachCount + $createurCount + $investisseurCount;
////
////        // Générer un jeton CSRF
////        $csrfToken = $csrfTokenManager->getToken('reject_user')->getValue();
////
////        return $this->render('admin/dashboard.html.twig', [
////            'users' => $users,
////            'adherentCount' => $adherentCount,
////            'coachCount' => $coachCount,
////            'createurCount' => $createurCount,
////            'investisseurCount' => $investisseurCount,
////            'totalUsers' => $totalUsers,
////            'csrf_token' => $csrfToken,
////        ]);
////    }
////
////    #[Route('/admin/validate/{id}', name: 'admin_validate_user', methods: ['POST'])]
////    public function validateUser(int $id, EntityManagerInterface $em): Response
////    {
////        $user = $em->getRepository(User::class)->find($id);
////        if (!$user) {
////            $this->addFlash('error', 'Utilisateur non trouvé.');
////            return $this->redirectToRoute('admin_dashboard');
////        }
////
////        if ($user instanceof \App\Entity\Coach) {
////            $user->setCertificat_valide(true);
////        } elseif ($user instanceof \App\Entity\InvestisseurProduit || $user instanceof \App\Entity\CreateurEvenement) {
////            $user->setCertificatValide(true);
////        }
////
////        $em->persist($user);
////        $em->flush();
////
////        $this->addFlash('success', 'Utilisateur validé avec succès.');
////        return $this->redirectToRoute('admin_dashboard');
////    }
////
//////    #[Route('/admin/reject/{id}', name: 'admin_reject_user', methods: ['POST'])]
//////    public function rejectUser(int $id, EntityManagerInterface $em): Response
//////    {
//////        $user = $em->getRepository(User::class)->find($id);
//////        if (!$user) {
//////            $this->addFlash('error', 'Utilisateur non trouvé.');
//////            return $this->redirectToRoute('admin_dashboard');
//////        }
//////
//////        $em->remove($user);
//////        $em->flush();
//////
//////        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
//////        return $this->redirectToRoute('admin_dashboard');
//////    }
////    #[Route('/admin/reject/{id}', name: 'admin_reject_user', methods: ['POST'])]
////    public function rejectUser(int $id, EntityManagerInterface $em): Response
////    {
////        $user = $em->getRepository(User::class)->find($id);
////        if (!$user) {
////            return new Response('Utilisateur non trouvé', 404);
////        }
////
////        try {
////            $em->remove($user);
////            $em->flush();
////            return new Response('Utilisateur supprimé avec succès', 200);
////        } catch (\Exception $e) {
////            return new Response('Erreur lors de la suppression : ' . $e->getMessage(), 500);
////        }
////    }
////}
//
//
//namespace App\Controller;
//
//use App\Entity\User;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
//use App\Entity\Coach;
//use App\Entity\InvestisseurProduit;
//use App\Entity\CreateurEvenement;
//use App\Service\SmsSender;
//use Twilio\Exceptions\TwilioException;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Security\Csrf\CsrfToken;
//
//class AdminController extends AbstractController
//{
//    #[Route('/admin', name: 'admin_dashboard')]
//    #[Route('/admin/requests', name: 'admin_requests')]
//    public function showRequests(EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
//    {
//        // Restreindre l'accès aux admins
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
//
//        $coachRepo = $em->getRepository('App\Entity\Coach');
//        $investisseurRepo = $em->getRepository('App\Entity\InvestisseurProduit');
//        $createurRepo = $em->getRepository('App\Entity\CreateurEvenement');
//
//        $coachs = $coachRepo->findBy(['Certificat_valide' => false]);
//        $investisseurs = $investisseurRepo->findBy(['certificatValide' => false]);
//        $createurs = $createurRepo->findBy(['certificatValide' => false]);
//        $users = array_merge($coachs, $investisseurs, $createurs);
//
//        $adherentCount = $em->createQueryBuilder()
//            ->select('COUNT(u.id)')
//            ->from('App\Entity\Adherent', 'u')
//            ->getQuery()
//            ->getSingleScalarResult();
//
//        $coachCount = $em->createQueryBuilder()
//            ->select('COUNT(u.id)')
//            ->from('App\Entity\Coach', 'u')
//            ->where('u.Certificat_valide = :certificatValide')
//            ->setParameter('certificatValide', true)
//            ->getQuery()
//            ->getSingleScalarResult();
//
//        $createurCount = $em->createQueryBuilder()
//            ->select('COUNT(u.id)')
//            ->from('App\Entity\CreateurEvenement', 'u')
//            ->where('u.certificatValide = :certificatValide')
//            ->setParameter('certificatValide', true)
//            ->getQuery()
//            ->getSingleScalarResult();
//
//        $investisseurCount = $em->createQueryBuilder()
//            ->select('COUNT(u.id)')
//            ->from('App\Entity\InvestisseurProduit', 'u')
//            ->where('u.certificatValide = :certificatValide')
//            ->setParameter('certificatValide', true)
//            ->getQuery()
//            ->getSingleScalarResult();
//
//        $totalUsers = $adherentCount + $coachCount + $createurCount + $investisseurCount;
//
//        // Générer un jeton CSRF
//        $csrfToken = $csrfTokenManager->getToken('reject_user')->getValue();
//
//        return $this->render('admin/users.html.twig', [
//            'users' => $users,
//            'adherentCount' => $adherentCount,
//            'coachCount' => $coachCount,
//            'createurCount' => $createurCount,
//            'investisseurCount' => $investisseurCount,
//            'totalUsers' => $totalUsers,
//            'csrf_token' => $csrfToken,
//        ]);
//    }
//
//
////    #[Route('/admin/validate/{id}', name: 'admin_validate_user', methods: ['POST'])]
////    public function validateUser(int $id, EntityManagerInterface $em): Response
////    {
////        // Restreindre l'accès aux admins
////        $this->denyAccessUnlessGranted('ROLE_ADMIN');
////
////        $user = $em->getRepository(User::class)->find($id);
////        if (!$user) {
////            $this->addFlash('error', 'Utilisateur non trouvé.');
////            return $this->redirectToRoute('admin_dashboard');
////        }
////
////        if ($user instanceof \App\Entity\Coach) {
////            $user->setCertificat_valide(true);
////        } elseif ($user instanceof \App\Entity\InvestisseurProduit || $user instanceof \App\Entity\CreateurEvenement) {
////            $user->setCertificatValide(true);
////        }
////
////        $em->persist($user);
////        $em->flush();
////
////        $this->addFlash('success', 'Utilisateur validé avec succès.');
////        return $this->redirectToRoute('admin_dashboard');
////    }
//
//    #[Route('/admin/validate/{id}', name: 'admin_validate_user', methods: ['POST'])]
//    public function validateUser(int $id, EntityManagerInterface $em, SmsSender $smsSender): Response
//    {
//        // Restreindre l'accès aux admins
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
//
//        $user = $em->getRepository(User::class)->find($id);
//        if (!$user) {
//            $this->addFlash('error', 'Utilisateur non trouvé.');
//            return $this->redirectToRoute('admin_dashboard');
//        }
//
//        // Valider l'utilisateur
//        if ($user instanceof Coach) {
//            $user->setCertificat_valide(true);
//        } elseif ($user instanceof InvestisseurProduit || $user instanceof CreateurEvenement) {
//            $user->setCertificatValide(true);
//
//            // Récupérer le numéro de téléphone
//            $telephone = $user instanceof InvestisseurProduit ? $user->getTelephoneInvestisseur() : $user->getTelephoneCreateur();
//
//            if (!$telephone) {
//                $this->addFlash('warning', 'Utilisateur validé, mais numéro de téléphone manquant.');
//            } else {
//                // Envoyer le SMS
//                $message = sprintf('Bonjour %s, votre inscription a été validée ! Connectez-vous sur notre plateforme.', $user->getNom() ?? 'Utilisateur');
//                $smsSent = $smsSender->sendSms($telephone, $message);
//
//                if (!$smsSent) {
//                    $this->addFlash('warning', 'Utilisateur validé, mais le SMS n\'a pas pu être envoyé.');
//                }
//            }
//        } else {
//            $this->addFlash('error', 'Type d\'utilisateur non valide.');
//            return $this->redirectToRoute('admin_dashboard');
//        }
//
//        $em->persist($user);
//        $em->flush();
//
//        $this->addFlash('success', 'Utilisateur validé avec succès.');
//        return $this->redirectToRoute('admin_dashboard');
//    }
//
//
//    #[Route('/admin/reject/{id}', name: 'admin_reject_user', methods: ['POST'])]
//    public function rejectUser(int $id, EntityManagerInterface $em): Response
//    {
//        // Restreindre l'accès aux admins
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
//
//        $user = $em->getRepository(User::class)->find($id);
//        if (!$user) {
//            return new Response('Utilisateur non trouvé', 404);
//        }
//
//        try {
//            $em->remove($user);
//            $em->flush();
//            return new Response('Utilisateur supprimé avec succès', 200);
//        } catch (\Exception $e) {
//            return new Response('Erreur lors de la suppression : ' . $e->getMessage(), 500);
//        }
//    }
//}


namespace App\Controller;

use App\Entity\User;
use App\Entity\Coach;
use App\Entity\InvestisseurProduit;
use App\Entity\CreateurEvenement;
use App\Service\SmsSender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    #[Route('/admin/requests', name: 'admin_requests')]
    public function showRequests(EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Restreindre l'accès aux admins
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $coachRepo = $em->getRepository('App\Entity\Coach');
        $investisseurRepo = $em->getRepository('App\Entity\InvestisseurProduit');
        $createurRepo = $em->getRepository('App\Entity\CreateurEvenement');

        // Utiliser la bonne casse pour chaque entité (comme dans le code fourni)
        $coachs = $coachRepo->findBy(['Certificat_valide' => false]);
        $investisseurs = $investisseurRepo->findBy(['certificatValide' => false]);
        $createurs = $createurRepo->findBy(['certificatValide' => false]);
        $users = array_merge($coachs, $investisseurs, $createurs);

        $adherentCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\Adherent', 'u')
            ->getQuery()
            ->getSingleScalarResult();

        $coachCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\Coach', 'u')
            ->where('u.Certificat_valide = :certificatValide')
            ->setParameter('certificatValide', true)
            ->getQuery()
            ->getSingleScalarResult();

        $createurCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\CreateurEvenement', 'u')
            ->where('u.certificatValide = :certificatValide')
            ->setParameter('certificatValide', true)
            ->getQuery()
            ->getSingleScalarResult();

        $investisseurCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\InvestisseurProduit', 'u')
            ->where('u.certificatValide = :certificatValide')
            ->setParameter('certificatValide', true)
            ->getQuery()
            ->getSingleScalarResult();

        $totalUsers = $adherentCount + $coachCount + $createurCount + $investisseurCount;

        // Générer un jeton CSRF
        $csrfToken = $csrfTokenManager->getToken('reject_user')->getValue();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'adherentCount' => $adherentCount,
            'coachCount' => $coachCount,
            'createurCount' => $createurCount,
            'investisseurCount' => $investisseurCount,
            'totalUsers' => $totalUsers,
            'csrf_token' => $csrfToken,
        ]);
    }

    #[Route('/admin/validate/{id}', name: 'admin_validate_user', methods: ['POST'])]
    public function validateUser(int $id, EntityManagerInterface $em, SmsSender $smsSender, Request $request): Response
    {
        // Restreindre l'accès aux admins
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Vérifier le jeton CSRF
        if (!$this->isCsrfTokenValid('admin_validate_user' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_dashboard');
        }

        // Valider l'utilisateur en tenant compte de la casse (comme dans le code fourni)
        if ($user instanceof Coach) {
            $user->setCertificat_valide(true);
        } elseif ($user instanceof InvestisseurProduit || $user instanceof CreateurEvenement) {
            $user->setCertificatValide(true);

            // Récupérer le numéro de téléphone
            $telephone = $user instanceof InvestisseurProduit ? $user->getTelephoneInvestisseur() : $user->getTelephoneCreateur();

            if (!$telephone) {
                $this->addFlash('warning', 'Utilisateur validé, mais numéro de téléphone manquant.');
            } else {
                try {
                    // Ajouter le préfixe international (ex. +33 pour la France)
                    if (!str_starts_with($telephone, '+')) {
                        $telephone = '+216' . $telephone; // Ajuste selon le pays
                    }

                    // Vérifier que getNom() existe, sinon utiliser une valeur par défaut
                    $nom = method_exists($user, 'getNom') ? $user->getNom() : 'Utilisateur';
                    $message = sprintf('Bonjour %s, votre inscription a été validée, vous etes officiellement membre de la communauté Coachini ! Connectez-vous sur notre plateforme.', $nom);
                    $smsSent = $smsSender->sendSms($telephone, $message);

                    if (!$smsSent) {
                        $this->addFlash('warning', 'Utilisateur validé, mais le SMS n\'a pas pu être envoyé.');
                    }
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Utilisateur validé, mais une erreur est survenue lors de l\'envoi du SMS : ' . $e->getMessage());
                }
            }
        } else {
            $this->addFlash('error', 'Type d\'utilisateur non valide.');
            return $this->redirectToRoute('admin_dashboard');
        }

        try {
            $em->persist($user);
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la sauvegarde de l\'utilisateur : ' . $e->getMessage());
            return $this->redirectToRoute('admin_dashboard');
        }

        $this->addFlash('success', 'Utilisateur validé avec succès.');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/reject/{id}', name: 'admin_reject_user', methods: ['POST'])]
    public function rejectUser(int $id, EntityManagerInterface $em, Request $request): Response
    {
        // Restreindre l'accès aux admins
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Vérifier le jeton CSRF
        if (!$this->isCsrfTokenValid('admin_reject' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_dashboard');
        }

        try {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_dashboard');
    }
}
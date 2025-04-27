<?php
//
//namespace App\Controller;
//
//use App\Entity\User;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
//
//class AdminController extends AbstractController
//{
//    #[Route('/admin', name: 'admin_dashboard')]
//    #[Route('/admin/requests', name: 'admin_requests')]
//    public function showRequests(EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
//    {
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
//        return $this->render('admin/dashboard.html.twig', [
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
//    #[Route('/admin/validate/{id}', name: 'admin_validate_user', methods: ['POST'])]
//    public function validateUser(int $id, EntityManagerInterface $em): Response
//    {
//        $user = $em->getRepository(User::class)->find($id);
//        if (!$user) {
//            $this->addFlash('error', 'Utilisateur non trouvé.');
//            return $this->redirectToRoute('admin_dashboard');
//        }
//
//        if ($user instanceof \App\Entity\Coach) {
//            $user->setCertificat_valide(true);
//        } elseif ($user instanceof \App\Entity\InvestisseurProduit || $user instanceof \App\Entity\CreateurEvenement) {
//            $user->setCertificatValide(true);
//        }
//
//        $em->persist($user);
//        $em->flush();
//
//        $this->addFlash('success', 'Utilisateur validé avec succès.');
//        return $this->redirectToRoute('admin_dashboard');
//    }
//
////    #[Route('/admin/reject/{id}', name: 'admin_reject_user', methods: ['POST'])]
////    public function rejectUser(int $id, EntityManagerInterface $em): Response
////    {
////        $user = $em->getRepository(User::class)->find($id);
////        if (!$user) {
////            $this->addFlash('error', 'Utilisateur non trouvé.');
////            return $this->redirectToRoute('admin_dashboard');
////        }
////
////        $em->remove($user);
////        $em->flush();
////
////        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
////        return $this->redirectToRoute('admin_dashboard');
////    }
//    #[Route('/admin/reject/{id}', name: 'admin_reject_user', methods: ['POST'])]
//    public function rejectUser(int $id, EntityManagerInterface $em): Response
//    {
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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use App\Service\SmsSender;
use Twilio\Exceptions\TwilioException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;

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

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'adherentCount' => $adherentCount,
            'coachCount' => $coachCount,
            'createurCount' => $createurCount,
            'investisseurCount' => $investisseurCount,
            'totalUsers' => $totalUsers,
            'csrf_token' => $csrfToken,
        ]);
    }

//
//    #[Route('/admin/validate/{id}', name: 'admin_validate_user', methods: ['POST'])]
//    public function validateUser(int $id, EntityManagerInterface $em): Response
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
//        if ($user instanceof \App\Entity\Coach) {
//            $user->setCertificat_valide(true);
//        } elseif ($user instanceof \App\Entity\InvestisseurProduit || $user instanceof \App\Entity\CreateurEvenement) {
//            $user->setCertificatValide(true);
//        }
//
//        $em->persist($user);
//        $em->flush();
//
//        $this->addFlash('success', 'Utilisateur validé avec succès.');
//        return $this->redirectToRoute('admin_dashboard');
//    }
//    #[Route('/admin/validate/{id}', name: 'admin_validate_user' , methods: ['GET','POST'])]
//    public function validateUser(
//        int $id,
//        Request $request,
//        EntityManagerInterface $em,
//        SmsSender $smsSender,
//        CsrfTokenManagerInterface $csrf
//    ): Response {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
//
//        // CSRF
//        $submittedToken = $request->request->get('_token');
//        if (!$csrf->isTokenValid(new CsrfToken('admin_validate_user'.$id, $submittedToken))) {
//            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
//        }
//
//        // Charger l’utilisateur
//        $user = $em->getRepository(User::class)->find($id);
//        if (!$user) {
//            $this->addFlash('error', 'Utilisateur non trouvé.');
//            return $this->redirectToRoute('admin_dashboard');
//        }
//
//        // Valider le certificat
//        if ($user instanceof Coach) {
//            $user->setCertificat_valide(true);
//        } elseif ($user instanceof InvestisseurProduit || $user instanceof CreateurEvenement) {
//            $user->setCertificatValide(true);
//        }
//
//        $em->flush();
//
//        // Récupérer le téléphone
//        if ($user instanceof InvestisseurProduit) {
//            $phone = $user->getTelephoneInvestisseur();
//        } elseif ($user instanceof CreateurEvenement) {
//            $phone = $user->getTelephoneCreateur();
//        } else {
//            $phone = null;
//        }
//
//        if ($phone) {
//            try {
//                $smsSender->send('+216'.trim($phone), 'Félicitations ! Votre compte Coachini a été validé.');
//                $this->addFlash('success', 'Utilisateur validé et SMS envoyé avec succès.');
//            } catch (TwilioException $e) {
//                $this->addFlash('error', 'Validation OK, mais échec de l’envoi du SMS : '.$e->getMessage());
//            }
//        } else {
//            $this->addFlash('success', 'Utilisateur validé avec succès.'.(
//                $user instanceof Coach ? '' : ' Aucun téléphone pour envoi SMS.'
//                ));
//        }
//
//        return $this->redirectToRoute('admin_dashboard');
//    }



    #[Route('/admin/validate/{id}', name: 'admin_validate_user', methods: ['GET','POST'])]
    public function validateUser(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        SmsSender $smsSender,
        CsrfTokenManagerInterface $csrf
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // 1) Vérification CSRF
        $submittedToken = $request->request->get('_token');
        if (!$csrf->isTokenValid(new CsrfToken('admin_validate_user'.$id, $submittedToken))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        // 2) Charger l’utilisateur et valider
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($user instanceof Coach) {
            $user->setCertificat_valide(true);
        } elseif ($user instanceof InvestisseurProduit || $user instanceof CreateurEvenement) {
            $user->setCertificatValide(true);
        }
        $em->flush();

        // 3) Envoi SMS si numéro dispo
        $phone = $user instanceof InvestisseurProduit
            ? $user->getTelephoneInvestisseur()
            : ($user instanceof CreateurEvenement ? $user->getTelephoneCreateur() : null);

        if ($phone) {
            try {
                $smsSender->send('+216'.trim($phone), 'Félicitations ! Votre compte a été validé.');
                $this->addFlash('success', 'Utilisateur validé et SMS envoyé.');
            } catch (TwilioException $e) {
                $this->addFlash('error', 'Validation OK, mais pas de SMS : '.$e->getMessage());
            }
        } else {
            $this->addFlash('success', 'Utilisateur validé.'.($user instanceof Coach ? '' : ' Pas de téléphone pour SMS.'));
        }

        return $this->redirectToRoute('admin_dashboard');
    }
    #[Route('/admin/reject/{id}', name: 'admin_reject_user', methods: ['POST'])]
    public function rejectUser(int $id, EntityManagerInterface $em): Response
    {
        // Restreindre l'accès aux admins
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new Response('Utilisateur non trouvé', 404);
        }

        try {
            $em->remove($user);
            $em->flush();
            return new Response('Utilisateur supprimé avec succès', 200);
        } catch (\Exception $e) {
            return new Response('Erreur lors de la suppression : ' . $e->getMessage(), 500);
        }
    }
}
<?php
//namespace App\Controller;
//
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
//
//class SecurityController extends AbstractController
//{
//#[Route('/login', name: 'app_login')]
//public function login(AuthenticationUtils $authenticationUtils, SessionInterface $session): Response
//{
//$user = $this->getUser();
//$error = $authenticationUtils->getLastAuthenticationError();
//$lastUsername = $authenticationUtils->getLastUsername();
//
//if ($user) {
//// Préparer les données avec gestion des null
//$nom = $user->getNom() ?? 'Inconnu';
//$prenom = $user->getPrenom() ?? '';
//$fullName = trim($nom . ' ' . $prenom);
//// Récupérer le rôle depuis la colonne "discriminator"
//$role = $user->getDiscriminator();
//
//// 🔐 Stocker des infos personnalisées dans la session
//$session->set('user_fullname', $fullName);
//$session->set('user_role', $role);
//
//return $this->render('security/login.html.twig', [
//'last_username' => $lastUsername,
//'error' => $error,
//'user_fullname' => $fullName,
//'user_role' => $role,
//'is_authenticated' => true,
//]);
//}
//
//if ($error) {
//$this->addFlash('error', 'Échec de la connexion. Vérifiez votre email ou mot de passe.');
//}
//
//return $this->render('security/login.html.twig', [
//'last_username' => $lastUsername,
//'error' => $error,
//'is_authenticated' => false,
//]);
//}
//
//#[Route('/logout', name: 'app_logout')]
//public function logout(): void
//{
//throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
//}
//}


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, SessionInterface $session): Response
    {
        $user = $this->getUser();
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Récupérer la clé reCAPTCHA depuis les paramètres
       $recaptchaSiteKey = $this->getParameter('RECAPTCHA_SITE_KEY');

        if ($user) {
            // Préparer les données avec gestion des null
            $nom = $user->getNom() ?? 'Inconnu';
            $prenom = $user->getPrenom() ?? '';
            $fullName = trim($nom . ' ' . $prenom);
            // Récupérer le rôle depuis la colonne "discriminator"
            $role = $user->getDiscriminator();

            // 🔐 Stocker des infos personnalisées dans la session
            $session->set('user_fullname', $fullName);
            $session->set('user_role', $role);

            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error,
                'user_fullname' => $fullName,
                'user_role' => $role,
                'is_authenticated' => true,
                'recaptcha_site_key' => $recaptchaSiteKey, // Ajout de la clé reCAPTCHA
            ]);
        }

        if ($error) {
            $this->addFlash('error', 'Échec de la connexion. Vérifiez votre email ou mot de passe.');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'is_authenticated' => false,
            'recaptcha_site_key' => $recaptchaSiteKey, // Ajout de la clé reCAPTCHA
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
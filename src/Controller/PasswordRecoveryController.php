<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Mailjet\Client as MailjetClient;
use Mailjet\Resources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class PasswordRecoveryController extends AbstractController
{
    private $mailjet;
    private $users;
    private $hasher;
    private $em;

    public function __construct(
        MailjetClient $mailjet,
        UserRepository $users,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ) {
        $this->mailjet = $mailjet;
        $this->users = $users;
        $this->hasher = $hasher;
        $this->em = $em;
    }

    #[Route('/password/recover', name: 'password_recover', methods: ['POST'])]
    public function recover(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Email invalide'], 400);
        }

        // Générer un code à 6 chiffres
        try {
            $code = random_int(100000, 999999);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la génération du code : ' . $e->getMessage()], 500);
        }

        // Stocker en session
        $session = $request->getSession();
        $session->set('recovery_email', $email);
        $session->set('recovery_code', $code);

        // Envoyer l'e-mail avec Mailjet
        try {
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => 'farahbenyedderr@gmail.com',
                            'Name' => 'Coachini',
                        ],
                        'To' => [
                            [
                                'Email' => $email,
                            ],
                        ],
                        'TemplateID' => 6765931,
                        'TemplateLanguage' => true,
                        'Subject' => 'Votre code de récupération Coachini',
                        'Variables' => [
                            'CODE' => $code,
                        ],
                    ],
                ],
            ];

            $response = $this->mailjet->post(Resources::$Email, ['body' => $body]);

            if (!$response->success()) {
                return $this->json(['error' => 'Échec de l’envoi de l’e-mail via Mailjet : ' . json_encode($response->getData())], 500);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => 'Échec de l’envoi de l’e-mail : ' . $e->getMessage()], 500);
        }

        return $this->json(['status' => 'ok']);
    }

    #[Route('/password/verify', name: 'password_verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        $code = $request->request->get('code');
        if (!$code) {
            return $this->json(['error' => 'Code manquant'], 400);
        }

        $session = $request->getSession();
        $savedCode = $session->get('recovery_code');

        if ((string)$code === (string)$savedCode) {
            return $this->json(['valid' => true]);
        }

        return $this->json(['valid' => false]);
    }

    #[Route('/password/reset', name: 'password_reset', methods: ['POST'])]
    public function reset(Request $request): JsonResponse
    {
        $newPass = $request->request->get('newPassword');
        $code    = $request->request->get('code');
        $session = $request->getSession();

        if (!$newPass || !$code) {
            return $this->json(['error' => 'Champs manquants'], 400);
        }

        if (strlen($newPass) < 6) {
            return $this->json([
                'error' => 'Le mot de passe doit contenir au moins 6 caractères.'
            ], 400);
        }

        // ← utilisation de la même clé 'recovery_code'
        if ((string)$session->get('recovery_code') !== (string)$code) {
            return $this->json(['error' => 'Code invalide'], 400);
        }

        $email = $session->get('recovery_email');
        $user  = $this->users->findOneBy(['email' => $email]);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $hashed = $this->hasher->hashPassword($user, $newPass);
        $user->setPassword($hashed);
        $this->em->flush();

        // nettoyage avec la même clé
        $session->remove('recovery_code');
        $session->remove('recovery_email');

        return $this->json(['status' => 'password_updated']);
    }
}
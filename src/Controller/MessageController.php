<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\AgoraService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

final class MessageController extends AbstractController
{
    #[Route('/messagerie', name: 'messagerie_index')]
    public function index(): Response
    {
        return $this->render('message/index.html.twig');
    }

//    #[Route('/messagerie/token', name: 'messagerie_token', methods: ['POST'])]
//    public function generateToken(AgoraService $agoraService): JsonResponse
//    {
//        $channelName = 'test_channel';
//        $uid = random_int(1, 100000);
//        $appId = $agoraService->getAppId();
//
//        try {
//            $token = $agoraService->generateToken($channelName, $uid);
//            return new JsonResponse([
//                'token' => $token,
//                'channelName' => $channelName,
//                'uid' => $uid,
//                'appId' => $appId,
//            ]);
//        } catch (\Exception $e) {
//            return new JsonResponse(['error' => $e->getMessage()], 500);
//        }
//    }

    #[Route('/messagerie/users', name: 'messagerie_users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        try {
            $users = $userRepository->findAll();
            $data = array_map(fn($user) => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'image' => $user->getImage(),
            ], $users);

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to fetch users: ' . $e->getMessage()], 500);
        }
    }
    #[Route('/messagerie/token', name: 'messagerie_token', methods: ['POST'])]
    public function token(AgoraService $agora): JsonResponse
    {
        try {
            $uid = random_int(1, 1e6);
            $channel = 'test_channel';
            $token = $agora->generateToken($channel, $uid);
            return $this->json([
                'token'       => $token,
                'channelName' => $channel,
                'uid'         => $uid,
            ]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/messages/history/{id}', name: 'api_messages_history', methods: ['GET'])]
    public function history(int $id, MessageRepository $repo): JsonResponse
    {
        try {
            $currentUser = $this->getUser();
            if (!$currentUser) {
                return $this->json(['error' => 'Utilisateur non authentifié'], 401);
            }
            $currentUserId = $currentUser->getId();

            // On récupère les messages entre currentUser et l'utilisateur $id (dans les deux sens)
            $messages = $repo->findConversationBetweenUsers($currentUserId, $id);

            $data = array_map(fn($m) => [
                'sender' => $m->getFromUserId() === $currentUserId ? 'me' : 'other',
                'senderId' => $m->getFromUserId(),
                'message' => $m->getContent(),
                'timestamp' => $m->getTimestamp()->format(\DateTime::ATOM),
            ], $messages);

            return $this->json($data);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Impossible de charger l’historique : ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/messages', name: 'api_messages_send', methods: ['POST'])]
    public function send(\Symfony\Component\HttpFoundation\Request $req, MessageRepository $repo): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        // ... valider $data['toUserId'], $data['message'] ...
        try {
            $repo->saveMessage(
                from: $this->getUser()->getId(),
                to:   $data['toUserId'],
                text: $data['message']
            );
            return $this->json(['status'=>'ok']);
        } catch (\Throwable $e) {
            return $this->json(['error'=>'Impossible de sauvegarder'], 500);
        }
    }
}

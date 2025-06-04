<?php
namespace App\Controller;

use App\Repository\PanierproduitRepository;
use App\Repository\PanierRepository;
use App\Service\SmsSender;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PaiementProduitController extends AbstractController
{
private StripeService $stripeService;
private Security $security;
private LoggerInterface $logger;

public function __construct(StripeService $stripeService, Security $security, LoggerInterface $logger)
{
$this->stripeService = $stripeService;
$this->security = $security;
$this->logger = $logger;
}

#[Route('/panier/payer', name: 'panier_payer', methods: ["GET"])]
public function payerPanier(PanierproduitRepository $panierproduitRepo, PanierRepository $panierRepo): Response
{
$user = $this->getUser();
if (!$user) {
$this->addFlash('error', 'Veuillez vous connecter pour accéder au paiement.');
return $this->redirectToRoute('app_login');
}

$panier = $panierRepo->findPanierByUser($user);
if (!$panier) {
$this->addFlash('warning', 'Votre panier est vide.');
return $this->redirectToRoute('panier_afficher');
}

$items = $panierproduitRepo->findBy(['panier' => $panier]);

$total = 0;
foreach ($items as $it) {
$total += $it->getQuantite() * $it->getProduit()->getPrix();
}

$publicKey = $this->getParameter('stripe.public_key');
$this->logger->info('Clé publique Stripe utilisée : ' . $publicKey);

return $this->render('panier/paiementProduit.html.twig', [
'items' => $items,
'total' => $total,
'publicKey' => $publicKey,
]);
}

#[Route('/panier/create-payment-intent', name: 'panier_create_payment_intent', methods: ["POST"])]
public function createPaymentIntent(Request $request, PanierproduitRepository $panierproduitpRepo, PanierRepository $panierRepo): JsonResponse
{
$data = $request->toArray();
$phone = $data['phone'] ?? null;
$this->logger->info('createPaymentIntent - phone reçu : ' . ($phone ?? 'Aucun'));

$user = $this->getUser();
$panier = $panierRepo->findPanierByUser($user);
if (!$panier) {
return new JsonResponse(['error' => 'Panier vide'], 400);
}

$items = $panierproduitpRepo->findBy(['panier' => $panier]);

$amount = 0;
foreach ($items as $it) {
$amount += $it->getQuantite() * $it->getProduit()->getPrix() * 100;
}

try {
$paymentIntent = $this->stripeService->createPaymentIntent(
(int)$amount,
'eur',
[
'userId' => $user->getId(),
'phone' => $phone
]
);
return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);
} catch (\Exception $e) {
$this->logger->error('Erreur création PaymentIntent : ' . $e->getMessage());
return new JsonResponse(['error' => $e->getMessage()], 500);
}
}

#[Route('/paiement/success', name: 'paiement_success', methods: ['GET'])]
public function success(Request $request, PanierproduitRepository $panierProduitRepo,PanierRepository $panierRepo,EntityManagerInterface $entityManager, NotifierInterface $notifier, SmsSender $smsSender): Response
{
$piId = $request->query->get('payment_intent');
if (!$piId) {
$this->addFlash('error', 'ID du PaymentIntent manquant.');
return $this->redirectToRoute('app_home');
}
try {
// Vérifier le statut du PaymentIntent
$this->logger->info('Récupération du PaymentIntent : ' . $piId);
$pi = $this->stripeService->retrievePaymentIntent($piId);
if ($pi->status !== 'succeeded') {
$this->addFlash('error', 'Le paiement n’a pas été validé.');
return $this->redirectToRoute('app_afficher_panier');
}

// Récupérer le numéro de téléphone depuis les métadonnées ou utiliser le numéro par défaut
$phone = $pi->metadata['phone'] ?? null;
$defaultPhone = '+21627700219';
if (!$phone) {
$this->logger->warning('Numéro de téléphone non trouvé dans les métadonnées, utilisation du numéro par défaut : ' . $defaultPhone);
$phone = $defaultPhone;
} else {
$this->logger->info('Numéro de téléphone utilisé pour l’envoi du SMS : ' . $phone);
}

$user = $this->getUser();
$panier = $panierRepo->findPanierByUser($user);
if (!$panier) {
$this->addFlash('error', 'Aucun panier trouvé pour l’utilisateur.');
return $this->redirectToRoute('app_afficher_panier');
}

$items = $panierProduitRepo->findBy(['panier' => $panier]);
$total = 0;
$lines = ["✅ Votre commande a été payée :"];

foreach ($items as $item) {
$quantite = $item->getQuantite();
$produit = $item->getProduit();
$prix = $produit->getPrix();
$subtotal = $quantite * $prix;
$total += $subtotal;
$item->setEtatPaiement('Payé');

$currentStock = $produit->getQuantite();
if ($currentStock < $quantite) {
$this->logger->error(sprintf('Stock insuffisant pour le produit : %s (demandé: %d, en stock: %d)', $produit->getNom(), $quantite, $currentStock));
$this->addFlash('error', sprintf('Stock insuffisant pour le produit : %s', $produit->getNom()));
return $this->redirectToRoute('app_afficher_panier');
}
$produit->setQuantite($currentStock - $quantite);
$entityManager->persist($produit);

$lines[] = sprintf("%s x%d = %s DT", $produit->getNom(), $quantite, number_format($subtotal, 2));
}
$lines[] = sprintf("Total : %s DT", number_format($total, 2));
$lines[] = "Le : " . (new \DateTime())->format('d/m/Y H:i');

$this->logger->info('Mise à jour du stock et du panier terminée, flush en cours');
$entityManager->flush();

// Envoi du SMS
try {
// Ajouter le préfixe international si manquant (sauf si c'est le numéro par défaut)
if ($phone !== $defaultPhone && !str_starts_with($phone, '+')) {
$phone = '+216' . ltrim($phone, '0');
$this->logger->info('Numéro après ajout du préfixe : ' . $phone);
}

// Valider le format du numéro
if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
$this->logger->error('Numéro de téléphone invalide : ' . $phone);
$this->addFlash('warning', 'Numéro de téléphone invalide, SMS non envoyé.');
} else {
$nom = method_exists($user, 'getNom') ? $user->getNom() : 'Cher client';
$message = sprintf(
'Bonjour %s, votre commande a été validée et est en cours de traitement. Total : %s DT. Merci pour votre achat !',
$nom,
number_format($total, 2)
);
$this->logger->info('Message SMS à envoyer : ' . $message);

$smsSent = $smsSender->sendSms($phone, $message);
if ($smsSent) {
$this->logger->info('SMS de confirmation de commande envoyé au : ' . $phone);
$this->addFlash('success', 'Votre commande a été validée et un SMS de confirmation vous a été envoyé.');
} else {
$this->logger->warning('SMS de confirmation de commande n\'a pas pu être envoyé au : ' . $phone);
$this->addFlash('warning', 'Votre commande a été validée, mais l\'envoi du SMS de confirmation a échoué.');
}
}
} catch (\Exception $e) {
$this->logger->error('Erreur lors de l\'envoi du SMS de confirmation : ' . $e->getMessage());
$this->addFlash('warning', 'Votre commande a été validée, mais une erreur est survenue lors de l\'envoi du SMS de confirmation : ' . $e->getMessage());
}

// Supprimer les PanierProduit associés avant de supprimer le Panier
$this->logger->info('Suppression des PanierProduit pour le panier ID : ' . $panier->getId());
foreach ($items as $item) {
$entityManager->remove($item);
}
$entityManager->flush();

// Supprimer le panier après un paiement réussi
$this->logger->info('Suppression du panier pour l’utilisateur : ' . $user->getId());
$entityManager->remove($panier);
$entityManager->flush();

$this->addFlash('success', 'Votre commande a bien été prise en compte et un SMS de confirmation vous a été envoyé.');
return $this->redirectToRoute('app_afficher_panier');

} catch (\Exception $e) {
$this->logger->error('Erreur lors du traitement du paiement réussi : ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
$this->addFlash('error', 'Une erreur est survenue lors de la validation de votre paiement : ' . $e->getMessage());
return $this->redirectToRoute('app_afficher_panier');
}
}
#[Route('/paiement/cancel', name: 'paiement_cancel', methods: ["GET"])]
public function cancel(): Response
{
$this->addFlash('error', 'Le paiement a été annulé.');
return $this->redirectToRoute('app_afficher_panier');
}

}
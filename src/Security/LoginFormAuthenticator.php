<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private HttpClientInterface $httpClient;
    private string $recaptchaSecret;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        HttpClientInterface   $httpClient,
        ParameterBagInterface $params
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->httpClient = $httpClient;
        $this->recaptchaSecret = $params->get('RECAPTCHA_SECRET_KEY');
    }

    public function authenticate(Request $request): Passport
    {
        // 1) Récupération et validation reCAPTCHA v2
        $captchaToken = $request->request->get('g-recaptcha-response', '');

        $response = $this->httpClient->request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            ['body' => [
                'secret'   => $this->recaptchaSecret,
                'response' => $captchaToken,
                'remoteip' => $request->getClientIp(),
            ]]
        );
        $data = $response->toArray();

        // Pour v2, on n’évalue que "success"
        if (empty($data['success'])) {
            throw new CustomUserMessageAuthenticationException(
                'Échec du captcha, veuillez cocher “Je ne suis pas un robot”.'
            );
        }

        // 2) Suite de l’authentification classique
        $email = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirection vers la page de login pour déclencher ta popup SweetAlert
        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}

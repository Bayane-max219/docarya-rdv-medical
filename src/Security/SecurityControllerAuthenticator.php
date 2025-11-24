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
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SecurityControllerAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator) {}

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirection en fonction du rôle
        $user = $token->getUser();

        // Vérifier si l'utilisateur est vérifié
        if (!$user->isVerified()) {
            // Rediriger vers la page "en attente de confirmation"
            return new RedirectResponse($this->urlGenerator->generate('app_pending_verification'));
        }

        if (in_array('ROLE_ADMINISTRATEUR', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_home_user'));
        }

        if (in_array('ROLE_PROFESSIONNEL_DE_SANTE', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_home_user'));
        }

        if (in_array('ROLE_PATIENT', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_home_user'));
        }

        // Redirection par défaut pour les autres rôles
        return new RedirectResponse($this->urlGenerator->generate('app_public_home'));
    }
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}

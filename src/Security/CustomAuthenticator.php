<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class CustomAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private UserRepository $userRepository,
        private RouterInterface $router
    ) {}

    public function authenticate(Request $request): Passport
    {
        $identifier = $request->request->get('_username');
        $password = $request->request->get('_password');
        $csrfToken = $request->request->get('_csrf_token');

        return new Passport(
            new UserBadge($identifier, function($identifier) {
                // Cherche par email OU par username
                $user = $this->userRepository->findOneBy(['email' => $identifier])
                    ?? $this->userRepository->findOneBy(['username' => $identifier]);

                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Identifiant ou mot de passe incorrect.');
                }

                return $user;
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirige vers la page accueil après connection
        return new RedirectResponse($this->router->generate('outing_list', [
            'id' => $token->getUser()->getId()
        ]));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }
}

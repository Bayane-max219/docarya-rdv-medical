<?php
// src/EventListener/VerifiedUserListener.php
namespace App\EventListener;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class VerifiedUserListener
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        
    // Vérifier que $user est une instance de App\Entity\User
    if (!$user instanceof \App\Entity\User) {
        throw new AccessDeniedException('Accès non autorisé.');
    }

        if (!$user->isVerified()) {
            $response = new RedirectResponse(
                $this->urlGenerator->generate('app_pending_verification')
            );
            $event->setResponse($response);
        }
    }
}
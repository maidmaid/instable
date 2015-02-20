<?php
// AuthenticationListener.php


namespace AppBundle\Event;

use AppBundle\Instable\Instable;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationListener implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var Instable */
    protected $instable;

    public function __construct(EntityManagerInterface $em, Instable $instable)
    {
        $this->em = $em;
        $this->instable = $instable;
    }

    public static function getSubscribedEvents()
    {
        return array(AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess');
    }

    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        /** @var OAuthToken $token */
        $token = $event->getAuthenticationToken();

        $raw = $token->getRawToken();
        $user = $this->instable->updateUser($raw['user']);
        $user->setAccessToken($token->getAccessToken());

        $this->em->persist($user);
        $this->em->flush();
    }
}

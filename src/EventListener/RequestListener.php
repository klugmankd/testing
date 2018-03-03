<?php

namespace App\EventListener;


use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RequestListener
{
    private $dispatcher;
    private $doctrine;
    private $tokenStorage;

    public function __construct(EventDispatcherInterface $dispatcher,
                                ManagerRegistry $doctrine,
                                TokenStorageInterface $tokenStorage)
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $token = $event->getRequest()
            ->headers
            ->get('Authorization');
        $userToken = $this->doctrine
            ->getRepository('App:UserToken')
            ->findOneBy(['token' => $token]);
        if (is_null($userToken)) {
            return;
        }
        $user = $userToken->getUser();
        $role = ($user->isAdmin()) ? ['ROLE_ADMIN'] : ['ROLE_USER'];
        $token = new UsernamePasswordToken($user, null, 'main', $role);
        $this->tokenStorage->setToken($token);
        $event1 = new InteractiveLoginEvent($event->getRequest(), $token);

        $this->dispatcher->dispatch('security.interactive_login', $event1);

    }
}
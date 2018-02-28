<?php

namespace App\Service;


use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Request;

class PauseManager
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function setPause(Request $request, User $user)
    {
        $testId = $request->get('test');
        $test = $this->doctrine
            ->getRepository('App:Test')
            ->find($testId);
        $user->setLastTest($test);
        $user->setTestOnPause(true);
        $entityManager = $this->doctrine
            ->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($test, 'json');
        return $jsonResponse;
    }

    public function getTest(User $user)
    {
        $userQuestions = $this->doctrine
            ->getRepository('App:UserQuestions')
            ->findBy([
                'user' => $user,
                'wasPassed' => false,
                'test' => $user->getLastTest()->getId()
            ]);
        $userQuestions['length'] = count($userQuestions);
        $userQuestions['id'] = $user->getLastTest()->getId();
        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($userQuestions, 'json');
        return $jsonResponse;
    }

}
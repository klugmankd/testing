<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Request;

class AnswerManager
{
    private $doctrine;
    private $test;
    private $user;
    private $entityManager;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function setAnswer(Request $request, User $user)
    {
        $testId = $request->get('test');
        $answers = $request->get('answers');
        $questionId = $request->get('question');

        $this->test = $this->doctrine
            ->getRepository('App:Test')
            ->find($testId);

        $this->entityManager = $this->doctrine
            ->getManager();
        $question = $this->doctrine
            ->getRepository('App:Question')
            ->find($questionId);
        $userQuestion = $this->doctrine
            ->getRepository('App:UserQuestions')
            ->findOneBy([
                'user' => $user,
                'question' => $question,
                'test' => $this->test
            ]);

        $userAnswers = json_encode($answers);
        $userQuestion->setAnswers($userAnswers);
        $userQuestion->setWasPassed(true);
        $this->entityManager->persist($userQuestion);
        $this->entityManager->flush();

        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($userQuestion, 'json');
        return $jsonResponse;
    }

}
<?php

namespace App\Service;


use App\Entity\UserPoints;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class CheckingManager
{

    private $user;

    private $test;

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param $id
     * @return \App\Entity\User|null|object
     */
    public function findUser($id)
    {
        $this->user = (!$this->user) ?
            $this->doctrine
                ->getRepository("App:User")
                ->find($id) :
            $this->getUser();

        return $this->user;
    }

    /**
     * @param $id
     * @return \App\Entity\Test|null|object
     */
    public function findTest($id)
    {
        $this->test = (!$this->test) ?
            $this->doctrine->getRepository("App:Test")
                ->find($id):
            $this->getTest();

        return $this->test;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }

    public function getParameters(Request $request)
    {
        $result['userId'] = $request->getSession()
            ->get('user')
            ->getId();
        $result['user'] = $this->doctrine
            ->getRepository('App:User')
            ->find($result['userId']);
        $result['test'] = $this->doctrine
            ->getRepository('App:Test')
            ->find($request->get('test'));
        $result['questions'] = $this->doctrine
            ->getRepository('App:Question')
            ->findCorrectAnswers($result['test']);
        $result['answers'] = $request->get('answers');

        return $result;
    }

    public function check(Request $request)
    {
        $parameters = $this->getParameters($request);
        $userResults = array();
        $userPoints = 0;
        foreach ($parameters['questions'] as $key => $question) {
            $correctAnswers = array();
            foreach ($question->getAnswers() as $answer) {
                $correctAnswers[] = $answer->getId();
            }
            $correctCount = count($correctAnswers);

            $userAnswerTrueCount = 0;
            $isAnswerCorrect = true;
            foreach ($parameters['answers'][$question->getId()] as $answer)
            {
                $answers[$question->getId()] = intval($answer);
                $isAnswerCorrect = $isAnswerCorrect && in_array($answer, $correctAnswers);
                if (in_array($answer, $correctAnswers)) {
                    $userAnswerTrueCount++;
                }
            }
            $userAnswerTrueCount = (!$isAnswerCorrect) ? 0 : $userAnswerTrueCount;

            $points = $question->getPoints();
            if ($userAnswerTrueCount != $correctCount) {
                $points = $question->getPoints() * ($userAnswerTrueCount / $correctCount);
            }
            $userPoints += $points;

            $userResults[$question->getId()]['points'] = $points;
            $userResults[$question->getId()]['result'] = $isAnswerCorrect;
            $userResults[$question->getId()]['userAnswers'] = $parameters['answers'][$question->getId()];
            $userResults[$question->getId()]['trueAnswers'] = $correctAnswers;
        }
        $questions['userResult'] = $userResults;
        $questions['userResult']['userPoints'] = $userPoints;
        $questions['user'] = $parameters['userId'];

        $isUserPresent = $this->doctrine
            ->getRepository("App:UserPoints")
            ->findOneBy([
                "direction" => $parameters['test']->getDirection()->getId(),
                "user" => $parameters['user']->getId()
            ]);
        $currentPoints = ($isUserPresent) ? $isUserPresent->getPoints() : 0;
        $userResult = ($isUserPresent) ? $isUserPresent : new UserPoints();
        $userResult->setUser($parameters['user']);
        $userResult->setDirection($parameters['test']->getDirection());
        $userResult->setPoints($userPoints + $currentPoints);
        $entityManager = $this->doctrine
            ->getManager();
        $entityManager->persist($userResult);
        $entityManager->flush();
        $questions['userResult']['result'] = $userResult->getPoints();
        return $questions;
    }
}
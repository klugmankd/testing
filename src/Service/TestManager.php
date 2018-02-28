<?php

namespace App\Service;


use App\Entity\Test;
use App\Entity\User;
use App\Entity\UserPoints;
use App\Entity\UserQuestions;
use Doctrine\Common\Persistence\ManagerRegistry;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestManager
{
    private $doctrine;
    private $test;
    private $user;
    private $entityManager;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function createTest($user, $direction, $difficulty, $questionsCount)
    {
        $this->entityManager = $this->doctrine->getManager();
        $this->user = $user;
        $this->initTest();
        $questions = $this->generateQuestionsList($direction, $difficulty, $questionsCount);
        $this->entityManager->flush();

        return $questions;
    }

    public function initTest()
    {
        $test = new Test();
        $test->setTime(60);
        $test->addUser($this->user);
        $this->user->setLastTest($test);
        $this->entityManager
            ->persist($test);
        $this->entityManager
            ->persist($this->user);
        $this->test = $test;
    }

    public function initQuestion($question)
    {
        $userQuestion = new UserQuestions();

        $userQuestion->setUser($this->user);
        $userQuestion->setTest($this->test);
        $userQuestion->setQuestion($question);
        $userQuestion->setAnswers('');
        $userQuestion->setWasPassed(false);

        return $userQuestion;
    }

    public function generateQuestionsList($direction, $difficulty, $questionsCount)
    {
        $questions = $this->doctrine
            ->getRepository('App:Question')
            ->findBy([
                "direction" => $direction,
                "difficulty" => $difficulty
            ]);
        $questionsMaxIndex = count($questions) - 1;
        $userQuestions = array();
        for ($index = 0; $index < $questionsCount; $index++) {
            $randIndex = rand(0, $questionsMaxIndex);
            $question = $questions[$randIndex];
            if (in_array($question, $userQuestions))
                continue;
            $userQuestion = $this->initQuestion($question);
            $this->entityManager->persist($userQuestion);
            $userQuestions[] = $userQuestion;
        }
        $userQuestions['length'] = count($userQuestions);
        $userQuestions['id'] = $this->test->getId();

        return $userQuestions;
    }

    public function check(Request $request, User $user)
    {
        $userQuestions = $this->getQuestions($request->get('test'), $user);
        $direction = $userQuestions[0]->getQuestion()->getDirection();

        $difficulty = $this->doctrine
            ->getRepository('App:Difficulty')
            ->findOneBy(['level' => 1]);

        $userPoints = $this->initPoints($user, $direction, $difficulty);

        $userPoints = $this->calculatePoints($userQuestions, $userPoints);

        $this->setLevel($userPoints);

        $entityManager = $this->doctrine
            ->getManager();
        $entityManager->persist($userPoints);
        $entityManager->flush();

        $serializer = SerializerBuilder::create()->build();
        $response = $serializer->serialize($userPoints, 'json');
        return $response;
    }

    public function initPoints($user, $direction, $difficulty)
    {
        $points = $this->doctrine
            ->getRepository('App:UserPoints')
            ->findOneBy([
                'direction' => $direction,
                'user' => $user
            ]);

        if (is_null($points)) {
            $points = new UserPoints();
            $points->setUser($user);
            $points->setDirection($direction);
            $points->setCurrentLevel($difficulty);
        }

        return $points;
    }

    public function getQuestions($testId, $user)
    {
        $test = $this->doctrine
            ->getRepository('App:Test')
            ->find($testId);
        $userQuestions = $this->doctrine
            ->getRepository('App:UserQuestions')
            ->findBy([
                'user' => $user,
                'test' => $test
            ]);

        return $userQuestions;
    }

    public function calculatePoints($userQuestions, UserPoints $userPoints)
    {
        $userAnswers = array();
        foreach ($userQuestions as $userQuestion) {
            $questionId = $userQuestion->getQuestion()->getId();
            foreach ($userQuestion->getQuestion()->getAnswers() as $answer) {
                if ($answer->isCorrect()) {
                    $userAnswers[$questionId]['trueAnswers'][] = $answer->getId();
                }
            }
            $userAnswers[$questionId]['userAnswers'] = json_decode($userQuestion->getAnswers());

            $correctAnswersCount = count($userAnswers[$questionId]['trueAnswers']);
            $isCorrect = true;
            $userCorrectAnswerCount = 0;
            foreach ($userAnswers[$questionId]['userAnswers'] as $userAnswer) {
                $condition = in_array($userAnswer, $userAnswers[$questionId]['trueAnswers']);
                $isCorrect = $isCorrect &&
                    in_array($userAnswer, $userAnswers[$questionId]['trueAnswers']);
                if ($condition) {
                    $userCorrectAnswerCount++;
                }
            }

            $questionPoints = $userQuestion->getQuestion()
                ->getPoints();
            $resultPoints = $questionPoints *
                ($userCorrectAnswerCount / $correctAnswersCount);
            $userPoints->setPoints($resultPoints + $userPoints->getPoints());
            $userAnswers[$questionId]['isCorrect'] = $isCorrect;
            $userAnswers[$questionId]['points'] = $resultPoints;
        }
        return $userPoints;
    }

    public function setLevel(UserPoints $userPoints)
    {
        $directionLevel = $this->doctrine
            ->getRepository('App:DirectionLevel')
            ->findOneBy([
                'direction' => $userPoints->getDirection(),
                'difficulty' => $userPoints->getCurrentLevel()
            ]);

        if ($directionLevel->getPoints() <= $userPoints->getPoints()) {
            $level = $this->doctrine
                ->getRepository('App:Difficulty')
                ->findByNextLevel($userPoints->getCurrentLevel()->getLevel());
            $userPoints->setCurrentLevel($level[0]);
        }
    }
}
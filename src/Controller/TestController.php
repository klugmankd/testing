<?php

namespace App\Controller;

use App\Entity\UserPoints;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TestController extends Controller
{
    /**
     * @Route("/test/check", name="test_check")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkAction(Request $request)
    {
        $userId = $request->getSession()->get('user')->getId();
        $user = $this->getDoctrine()
            ->getRepository('App:User')
            ->find($userId);
        $testId = $request->get('test');
        $test = $this->getDoctrine()
            ->getRepository('App:Test')
            ->find($testId);
        $userQuestions = $this->getDoctrine()
            ->getRepository('App:UserQuestions')
            ->findBy([
                'user' => $user,
                'test' => $test
            ]);
        $direction = $userQuestions[0]->getQuestion()->getDirection();
        $userPoints = $this->getDoctrine()
            ->getRepository('App:UserPoints')
            ->findOneBy([
                'direction' => $direction,
                'user' => $user
            ]);

        if (is_null($userPoints)) {
            $userPoints = new UserPoints();
            $userPoints->setUser($user);
            $userPoints->setDirection($direction);
            $userPoints->setPoints(0);
        }

        $userAnswers = array();
        foreach ($userQuestions as $userQuestion) {
            $questionId = $userQuestion->getQuestion()->getId();
            foreach ($userQuestion->getQuestion()->getAnswers() as $answer) {
                if ($answer->isCorrect()) {
                    $userAnswers[$questionId]['trueAnswers'][] = $answer->getId();
                }
            }
            $userAnswers[$questionId]['userAnswers'] = json_decode($userQuestion->getAnswers());

            $isCorrect = false;
            foreach ($userAnswers[$questionId]['userAnswers'] as $userAnswer) {
                $condition = in_array($userAnswer, $userAnswers[$questionId]['trueAnswers']);
                $isCorrect = $isCorrect ||
                    in_array($userAnswer, $userAnswers[$questionId]['trueAnswers']);
                if ($condition) {
                    $questionPoints = $userQuestion->getQuestion()
                        ->getPoints();
                    $resultPoints = $userPoints->getPoints() + $questionPoints;
                    $userPoints->setPoints($resultPoints);
                }
            }
            $userAnswers[$questionId]['isCorrect'] = $isCorrect;
        }
        $entityManager = $this->getDoctrine()
            ->getManager();
        $entityManager->persist($userPoints);
        $entityManager->flush();


        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($userAnswers, 'json');
        return $this->json(
            $jsonResponse
        );
    }
}

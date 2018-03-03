<?php

namespace App\Controller;

use App\Entity\Test;
use App\Entity\UserPoints;
use App\Entity\UserQuestions;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    /**
     * @Route("/", name="app_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function index(Request $request)
    {
        $user = $this->getUser();

        if ($user->isTestOnPause()) {
            $test = $this->getDoctrine()
                ->getRepository('App:UserQuestions')
                ->findOneBy(['test' => $user->getLastTest()]);
            $direction = $test->getQuestion()->getDirection();
            $difficulty = $test->getQuestion()->getDifficulty();
            return $this->render("home.html.twig", [
                "direction" => $direction,
                "difficulty" => $difficulty,
                "pause" => $user->isTestOnPause()
            ]);
        }

        return ($request->getSession()->get("user")) ?
            $this->render("home.html.twig", ["pause" => false]) :
            $this->redirect('/connect/google');
//        return $this->redirect("http://localhost:8080/#/directions");
    }

    /**
     * @Route("/directions", name="app_directions")
     */
    public function directions()
    {
        $directions = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findAll();

        return $this->render("choose-direction.html.twig", ["directions" => $directions]);
    }

    /**
     * @Route("/directions/{name}", name="app_difficulties")
     * @param $name
     * @return Response
     */
    public function difficulties($name)
    {
        $user = $this->getUser();

        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findOneBy(['name' => $name]);

        $userPoints = $this->getDoctrine()
            ->getRepository('App:UserPoints')
            ->findOneBy([
                'direction' => $direction,
                'user' => $user
            ]);
        $currentLevel = 1;
        if (!is_null($userPoints)) {
            $currentLevel = $userPoints->getCurrentLevel();
        }
        $difficulties = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->findByCurrentLevel($currentLevel);

        return $this->render("choose-difficulty.html.twig", [
            "difficulties" => $difficulties,
            "direction" => $name
        ]);
    }

    /**
     * @Route("/directions/{directionName}/{difficultyName}", name="app_test")
     * @param Request $request
     * @param $directionName
     * @param $difficultyName
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function test(Request $request, $directionName, $difficultyName)
    {
        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findOneBy(['name' => $directionName]);
        $difficulty = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->findOneBy(['name' => $difficultyName]);

        if ($request->isXmlHttpRequest()) {
// const
            $questionsCount = 5;

            $entityManager = $this->getDoctrine()
                ->getManager();

            $user = $this->getUser();

            $userQuestions = array();
//            if (!is_null($user->getLastTest())) {
//                $userQuestionsPassed = $this->getDoctrine()
//                    ->getRepository('App:UserQuestions')
//                    ->findBy([
//                        'user' => $user,
//                        'wasPassed' => true,
//                        'test' => $user->getLastTest()
//                    ]);
//                $userQuestionsAll = $this->getDoctrine()
//                    ->getRepository('App:UserQuestions')
//                    ->findBy([
//                        'user' => $user,
//                        'test' => $user->getLastTest()->getId()
//                    ]);
//
//                $userQuestions = (count($userQuestionsPassed) == $questionsCount) ?
//                    $userQuestionsAll : array();
//                $test = $user->getLastTest();
//            }

//            if (count($userQuestions) == 0) {
            $test = new Test();
            $test->setTime(60);
            $test->addUser($user);
            $user->setLastTest($test);
            $entityManager->persist($test);
            $entityManager->persist($user);

            $questions = $this->getDoctrine()
                ->getRepository('App:Question')
                ->findBy([
                    "direction" => $direction,
                    "difficulty" => $difficulty
                ]);

            $questionsMaxIndex = count($questions) - 1;

            for ($index = 0; $index < $questionsCount; $index++) {
                $randIndex = rand(0, $questionsMaxIndex);
                if (in_array($questions[$randIndex], $userQuestions)) continue;
                $userQuestion = new UserQuestions();
                $userQuestion->setUser($user);
                $userQuestion->setTest($test);
                $userQuestion->setQuestion($questions[$randIndex]);
                $userQuestion->setAnswers('');
                $userQuestion->setWasPassed(false);
                $entityManager->persist($userQuestion);
                $userQuestions[] = $userQuestion;
            }
            $entityManager->flush();
//            }
            $userQuestions['length'] = count($userQuestions);
            $userQuestions['id'] = $test->getId();
            $serializer = SerializerBuilder::create()->build();
            $jsonResponse = $serializer->serialize($userQuestions, 'json');
            return $this->json($jsonResponse);
        } else {
            return $this->render("test.html.twig", [
                "direction" => $directionName,
                "difficulty" => $difficultyName,
                "pause" => false
            ]);
        }

    }

    /**
     * @Route("/directions/{directionName}/{difficultyName}/pause", name="app_test_pause")
     * @param Request $request
     * @param $directionName
     * @param $difficultyName
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function returnToPause(Request $request, $directionName, $difficultyName)
    {
        $user = $this->getUser();

        if ($request->isXmlHttpRequest()) {
            $userQuestions = $this->getDoctrine()
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
            return $this->json($jsonResponse);
        } else {
            $user->setTestOnPause(false);
            $entityManager = $this->getDoctrine()
                ->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->render("test.html.twig", [
                "direction" => $directionName,
                "difficulty" => $difficultyName,
                "pause" => true
            ]);
        }
    }

    /**
     * @Route("/api/session")
     * @param Request $request
     * @return Response
     */
    public function userSession(Request $request)
    {/*
        $token = $this->getDoctrine()
            ->getRepository('App:UserToken')
            ->findOneBy(['user' => $this->getUser()])
            ->getToken();*/
        $token = $request->getSession()->get('token');
        return new Response($token);
    }
}

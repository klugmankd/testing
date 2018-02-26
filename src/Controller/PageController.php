<?php

namespace App\Controller;

use App\Entity\Test;
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
        return ($request->getSession()->get("user")) ?
            $this->render("home.html.twig"):
            $this->redirect('/connect/google');
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
        $difficulties = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->findAll();

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

            $questionsCount = 20;

            $entityManager = $this->getDoctrine()
                ->getManager();

            $userId = $request->getSession()
                ->get('user')
                ->getId();
            $user = $this->getDoctrine()
                ->getRepository('App:User')
                ->find($userId);
            $userQuestions = array();
            $test = 0;
            if (!empty($user->getLastTest())) {
                $userQuestionsPassed = $this->getDoctrine()
                    ->getRepository('App:UserQuestions')
                    ->findBy([
                        'user' => $user,
                        'wasPassed' => true,
                        'test' => $user->getLastTest()
                    ]);
                $userQuestionsAll = $this->getDoctrine()
                    ->getRepository('App:UserQuestions')
                    ->findBy([
                        'user' => $user,
                        'test' => $user->getLastTest()->getId()
                    ]);
                $userQuestions = (count($userQuestionsPassed) < $questionsCount) ?
                    $userQuestionsAll : array();
                $test = $user->getLastTest();
            }

            if (count($userQuestions) == 0) {
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
                    while (in_array($questions[$randIndex], $userQuestions)) {
                        $randIndex = rand(0, $questionsMaxIndex);
                    }
                    $userQuestion = new UserQuestions();
                    $userQuestion->setUser($user);
                    $userQuestion->setTest($test);
                    $userQuestion->setQuestion($questions[$randIndex]);
                    $userQuestion->setAnswers('');
                    $userQuestion->setWasPassed(false);
                    $entityManager->persist($userQuestion);
                }
                $entityManager->flush();
            }
            $userQuestions['length'] = count($userQuestions);
            $userQuestions['id'] = $test->getId();
            $serializer = SerializerBuilder::create()->build();
            $jsonResponse = $serializer->serialize($userQuestions, 'json');
            return $this->json($jsonResponse);
        } else {
            return $this->render("test.html.twig", [
                "direction" => $directionName,
                "difficulty" => $difficultyName
            ]);
        }

    }

    /**
     * @Route("/session")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function userSession(Request $request)
    {
        $userId = $request->getSession()
            ->get('user')
            ->getId();
        $user = $this->getDoctrine()
            ->getRepository('App:User')
            ->find($userId);
        return $this->json($user);
    }
}

<?php

namespace App\Controller;

use App\Entity\Test;
use App\Entity\UserPoints;
use App\Entity\UserQuestions;
use App\Service\AnswerManager;
use App\Service\PauseManager;
use App\Service\TestManager;
use App\Service\TokenManager;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    private $questionsCount = 20;
    private $testManager;
    private $answerManager;
    private $pauseManager;
    private $tokenManager;

    public function __construct(TestManager $testManager,
                                AnswerManager $answerManager,
                                PauseManager $pauseManager,
                                TokenManager $tokenManager)
    {
        $this->testManager = $testManager;
        $this->answerManager = $answerManager;
        $this->pauseManager = $pauseManager;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @Route("/api/test/{direction}/{difficulty}", name="app_test_create")
     * @Method({"POST"})
     * @param $direction
     * @param $difficulty
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction($direction, $difficulty)
    {
        $questions = $this->testManager
            ->createTest($this->getUser(),
                $direction,
                $difficulty,
                $this->questionsCount);

        return $this->json($questions['length'] > 0);
    }

    /**
     * @Route("/api/test/current")
     */
    public function currentTestAction()
    {
        $test = $this->getUser()
            ->getLastTest();

        $serializer = SerializerBuilder::create()->build();
        $response = $serializer->serialize($test, 'json');
        return $this->json($response);
    }

    /**
     * @Route("/api/test/answer")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function answerAction(Request $request)
    {
        if (!$request->get('questionIsLast')) {
            $response = $this->answerManager
                ->setAnswer($request, $this->getUser());
        } else {
            $response = $this->testManager
                ->check($request, $this->getUser());
        }

        return $this->json($response);
    }

    /**
     * @Route("/api/test/pause", name="save_test")
     * @Method({"PUT"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function pauseTestAction(Request $request)
    {
        $response = $this->pauseManager
            ->setPause($request, $this->getUser());

        return $this->json($response);
    }

    /**
     * @Route("/save-test", name="save_test")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function pauseAction(Request $request)
    {
        $response = $this->pauseManager
            ->setPause($request, $this->getUser());

        return $this->json($response);
    }

    /**
     * @Route("/test/check", name="test_check")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkAction(Request $request)
    {
        $response = $this->testManager
            ->check($request, $this->getUser());

        return $this->json($response);
    }

    /**
     * @Route("/test/route")
     */
    public function test()
    {
        $user = $this->getUser();
        $token = $this->tokenManager
            ->generateToken($user);

        return new Response($token);
    }
}

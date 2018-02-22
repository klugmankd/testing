<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyController extends Controller
{

    /**
     * @Route("/", name="app_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function home(Request $request)
    {
        return ($request->getSession()->get("user")) ?
            $this->render("home.html.twig"):
            $this->redirect('/connect/google');
    }

    /**
     * @Route("/choose-direction", name="choose_direction")
     */
    public function chooseDirection()
    {
        $directions = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findAll();

        return $this->render("choose-direction.html.twig", ["directions" => $directions]);
    }

    /**
     * @Route("/choose-difficulty/{direction}", name="choose_difficulty")
     * @param $direction
     * @param Request $request
     * @return Response
     */
    public function chooseDifficult($direction, Request $request)
    {
        $user = $request->getSession()
            ->get('user')
            ->getId();
        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->find($direction);
        $difficulties = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->findAll();
        $userResultsPassed = $this->getDoctrine()
            ->getRepository('App:UserResults')
            ->findByUser($user, $direction, true);
        $userResultsNotPassed = $this->getDoctrine()
            ->getRepository('App:UserResults')
            ->findByUser($user, $direction, false);
        $userPoints = $this->getDoctrine()
            ->getRepository('App:UserPoints')
            ->findOneBy([
                'direction' => $direction,
                'user' => $user
            ]);
        $tests = $this->getDoctrine()
            ->getRepository('App:Test')
            ->findBy(['direction' => $direction]);

        $testsByDifficulty = array();
        foreach ($difficulties as $id => $difficulty) {
            $testsByDifficulty[$difficulty->getId()] = array();
            $testHasQuestions = false;
            foreach ($tests as $key => $test) {
                if ($test->getDifficulty() == $difficulty) {
                    $testHasQuestions = $testHasQuestions || count($test->getQuestions()) > 0;
                    $testsByDifficulty[$difficulty->getId()][] = $test;
                    unset($tests[$key]);
                }
            }
            $userAccess = true;
            foreach ($testsByDifficulty[$difficulty->getId()] as $test) {
                $points = (!$userPoints) ? 0 : $userPoints->getPoints();
                $userAccess = $userAccess && ($points >= $test->getOccurrence());
            }
            $testsByDifficulty[$difficulty->getId()]['accessible'] = $userAccess;
            if (!$testHasQuestions) {
                unset($difficulties[$id]);
            }
        }


        $difficultiesPassed = array();
        if (count($userResultsPassed) > 0) {
            foreach ($userResultsPassed as $result) {
                $difficultiesPassed[] = $result->getTest()
                    ->getDifficulty();
            }
        }

        $difficultiesNotPassed = array();
        if (count($userResultsNotPassed) > 0) {
            foreach ($userResultsNotPassed as $result) {
                $difficultiesNotPassed[] = $result->getTest()
                    ->getDifficulty();
            }
        }

        foreach ($difficulties as $key => $difficulty) {
            $difficulties[0]->setAccessible(true);
            if ($key > 0) {
                $userAccessByPoints = $testsByDifficulty[$difficulty->getId()]['accessible'];
                $accessible = $difficulties[$key - 1]->hasPassed() && $userAccessByPoints;
                $difficulties[$key]->setAccessible($accessible);
            }
            if (count($difficultiesPassed) > 0) {
                $difficulties[$key]->setHasPassed(
                    in_array($difficulty, $difficultiesPassed)
                );
                $difficulties[$key]->setDidNotPass(false);
            } else {
            if (count($difficultiesNotPassed) > 0) {
                $difficulties[$key]->setHasPassed(
                    !in_array($difficulty, $difficultiesNotPassed)
                );
                $difficulties[$key]->setDidNotPass(false);
            }
            }
            if (!in_array($difficulty, $difficultiesPassed) &&
                !in_array($difficulty, $difficultiesNotPassed)) {
                $difficulties[$key]->setDidNotPass(true);
                $difficulties[$key]->setHasPassed(false);
            }
        }
        return $this->render("choose-difficulty.html.twig", [
            "difficulties" => $difficulties,
            "direction" => $direction->getId()
        ]);

    }



    /**
     * @Route("/start-test/{direction}/{difficulty}", name="start_test")
     * @param $difficulty
     * @param $direction
     * @return Response
     */
    public function startTest($difficulty, $direction)
    {
        return $this->render(
            'test.html.twig', [
                "difficulty" => $difficulty,
                "direction" => $direction
            ]
        );
    }

    /**
     *@Route("/start-test/session")
     */
    public function startTestSession(Request $request)
    {
        $session = $request->getSession();
//        $session->clear();
//        $session->save();
        return $this->json($session);
    }

//    public function logout()
//    {
//
//    }

    /**
     * @Route("/privacy-policy")
     */
    public function privacyPolicy()
    {
        return new Response("privacy-policy");
    }

    /**
     * @Route("/login")
     */
    public function login()
    {
        return $this->render("login.html.twig");
    }
}
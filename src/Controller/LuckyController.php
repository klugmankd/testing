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
        $userPoints = $this->getDoctrine()
            ->getRepository('App:UserPoints')
            ->findOneBy([
                'user' => $user,
                'direction' => $direction
            ]);
        $points = (!$userPoints) ? 0 : $userPoints->getPoints();
        $accessibleDifficulty = $this->getDoctrine()
            ->getRepository('App:Test')
            ->findAccessible($direction, $points)[0]
            ->getDifficulty();
        $difficulties = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->findAccessible($accessibleDifficulty);
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
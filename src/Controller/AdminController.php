<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController
 * @package App\Controller
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends Controller
{

    /**
     * @Route("/admin")
     */
    public function index(Request $request)
    {
        $user = $request->getSession()->get("user")->isAdmin();
        return $this->json($user);
    }

    /**
     * @Route("/admin/direction")
     */
    public function directions()
    {
        return $this->render('directions.html.twig');
    }

    /**
     * @Route("/admin/difficulty")
     */
    public function difficulties()
    {
        return $this->render('difficulties.html.twig');
    }

    /**
     * @Route("/admin/test")
     */
    public function test()
    {
        $directions = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findAll();

        $difficulties = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->findAll();

        return $this->render(
            'tests.html.twig',
            array(
                "directions" => $directions,
                "difficulties" => $difficulties
            )
        );
    }

    /**
     * @Route("/admin/question")
     */
    public function question()
    {
        $tests = $this->getDoctrine()
            ->getRepository('App:Test')
            ->findAll();

        return $this->render(
            'questions.html.twig',
            array(
                "tests" => $tests
            )
        );
    }

    /**
     * @Route("/admin/answer")
     */
    public function answer()
    {
        $questions = $this->getDoctrine()
            ->getRepository('App:Question')
            ->findAll();

        return $this->render(
            'answers.html.twig',
            array(
                "questions" => $questions
            )
        );
    }
}

<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyController extends Controller
{

    /**
     * @Route("/direction")
     */
    public function directions()
    {
        return $this->render('directions.html.twig');
    }

    /**
     * @Route("/difficulty")
     */
    public function difficulties()
    {
        return $this->render('difficulties.html.twig');
    }

    /**
     * @Route("/test")
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
     * @Route("/question")
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
     * @Route("/answer")
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

    /**
     *@Route("/start-test")
     */
    public function startTest()
    {
        return $this->render(
            'test.html.twig'
        );
    }
}
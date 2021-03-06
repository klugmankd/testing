<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Service\AnswerManager;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AnswerController extends Controller
{

    private $answerManager;

    public function __construct(AnswerManager $answerManager)
    {
        $this->answerManager = $answerManager;
    }

    /**
     * @Route("/answers", name="answers_create")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request)
    {
        $question = $this->getDoctrine()
            ->getRepository('App:Question')
            ->find($request->get('question'));

        $answer = new Answer();
        $answer->setText($request->get('text'));
        $answer->setIsCorrect($request->get('isCorrect'));
        $answer->setQuestion($question);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($answer);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "create",
                'parameter' => "name",
                'value' => $answer->getId(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/answers/{id}", name="answers_get_one")
     * @Method({"GET"})
     * @param int $id
     * @return Response
     */
    public function readAction($id)
    {
        $answer = $this->getDoctrine()
            ->getRepository('App:Answer')
            ->find($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($answer, 'json');
        return $this->json($jsonResponse);
    }

    /**
     * @Route("/answers", name="answers_get_all")
     * @Method({"GET"})
     */
    public function readAllAction()
    {
        $answers = $this->getDoctrine()
            ->getRepository('App:Answer')
            ->findAll();

        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($answers, 'json');
        return $this->json($jsonResponse);
    }

    /**
     * @Route("/answers/{id}", name="answers_update")
     * @Method({"PUT"})
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction($id, Request $request)
    {
        $answer = $this->getDoctrine()
            ->getRepository('App:Answer')
            ->find($id);

        $question = $this->getDoctrine()
            ->getRepository('App:Question')
            ->find($request->get('question'));

        $answer->setText($request->get('text'));
        $answer->setIsCorrect($request->get('isCorrect'));
        $answer->setQuestion($question);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($answer);
        $entityManager->flush();

        return $this->json(
            array(
                'action' => "update",
                'parameter' => "name",
                'value' => $answer->getId(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/answers/{id}", name="answers_delete")
     * @Method({"DELETE"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction($id)
    {
        $answer = $this->getDoctrine()
            ->getRepository('App:Answer')
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($answer);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "delete",
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/answers/check", name="answers_check")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkAnswerAction(Request $request)
    {
        $response = $this->answerManager
            ->setAnswer($request, $this->getUser());

        return $this->json($response);
    }

}

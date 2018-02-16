<?php

namespace App\Controller;

use JMS\Serializer\SerializerBuilder;
use App\Entity\Question;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QuestionController extends Controller
{
    /**
     * @Route("/questions", name="questions_create")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request)
    {
        $test = $this->getDoctrine()
            ->getRepository('App:Test')
            ->find($request->get('test'));

        $question = new Question();
        $question->setText($request->get('text'));
        $question->setTest($test);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($question);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "create",
                'parameter' => "name",
                'value' => $question->getId(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/questions/{id}", name="questions_get_one")
     * @Method({"GET"})
     * @param int $id
     * @return Response
     */
    public function readAction($id)
    {
        $question = $this->getDoctrine()
            ->getRepository('App:Question')
            ->find($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($question, 'json');
        return $this->json(
            array(
                "jsonResponse" => $jsonResponse,
                "stringifier" => true
            )
        );
    }

    /**
     * @Route("/questions", name="questions_get_all")
     * @Method({"GET"})
     */
    public function readAllAction()
    {
        $questions = $this->getDoctrine()
            ->getRepository('App:Question')
            ->findAll();

        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($questions, 'json');
        return $this->json(
            array(
                "jsonResponse" => $jsonResponse,
                "stringifier" => true
            )
        );
    }

    /**
     * @Route("/questions/{id}", name="questions_update")
     * @Method({"PUT"})
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction($id, Request $request)
    {
        $question = $this->getDoctrine()
            ->getRepository('App:Question')
            ->find($id);

        $test = $this->getDoctrine()
            ->getRepository('App:Test')
            ->find($request->get('test'));

        $question->setText($request->get('text'));
        $question->setTest($test);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($question);
        $entityManager->flush();

        return $this->json(
            array(
                'action' => "update",
                'parameter' => "name",
                'value' => $question->getId(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/questions/{id}", name="questions_delete")
     * @Method({"DELETE"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction($id)
    {
        $question = $this->getDoctrine()
            ->getRepository('App:Question')
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($question);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "delete",
                'result' => 'success'
            )
        );
    }
}

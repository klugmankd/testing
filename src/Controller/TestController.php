<?php

namespace App\Controller;

use App\Entity\Test;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    /**
     * @Route("/tests", name="tests_create")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request)
    {
        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->find($request->get('direction'));
        $difficulty = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->find($request->get('difficulty'));

        $test = new Test();
        $test->setDifficulty($difficulty);
        $test->setDirection($direction);
        $test->setBarrier($request->get('barrier'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($test);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "create",
                'parameter' => "id",
                'value' => $test->getId(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/tests/{id}", name="tests_get_one")
     * @Method({"GET"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function readAction($id)
    {
        $test = $this->getDoctrine()
            ->getRepository('App:Test')
            ->find($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($test, 'json');
        return $this->json($jsonResponse);
    }

    /**
     * @Route("/tests", name="tests_get_all")
     * @Method({"GET"})
     */
    public function readAllAction()
    {
        $tests = $this->getDoctrine()
            ->getRepository('App:Test')
            ->findAll();

        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($tests, 'json');
        return $this->json($jsonResponse);
    }

    /**
     * @Route("/tests/{id}", name="tests_update")
     * @Method({"PUT"})
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction($id, Request $request)
    {
        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->find($request->get('direction'));
        $difficulty = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->find($request->get('difficulty'));
        $test = $this->getDoctrine()
            ->getRepository('App:Test')
            ->find($id);

        $test->setDifficulty($difficulty);
        $test->setDirection($direction);
        $test->setBarrier($request->get('barrier'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($test);
        $entityManager->flush();

        return $this->json(
            array(
                'action' => "update",
                'parameter' => "id",
                'value' => $test->getId(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/tests/{id}", name="tests_delete")
     * @Method({"DELETE"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction($id)
    {
        $test = $this->getDoctrine()
            ->getRepository('App:Test')
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($test);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "delete",
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/tests/random/{direction}/{difficulty}", name="tests_get_random")
     * @Method({"GET"})
     * @param $direction
     * @param $difficulty
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function readRandomAction($direction, $difficulty)
    {
        $repository = $this->getDoctrine()
            ->getRepository('App:Test');

        $ids = $repository->findTestIds($direction, $difficulty);

        $arIds = array();
        foreach ($ids as $id)
            $arIds[] = $id;

        $index = rand(0, count($arIds) - 1);

        $id = $arIds[$index];

        $test = $repository->find($id);
        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($test, 'json');
        return $this->json($jsonResponse);
    }


    //check test route TODO
    /**
     * @Route("/tests/check", name="tests_check")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkAction(Request $request)
    {
        $test = $this->getDoctrine()
            ->getRepository('App:Test')
            ->find($request->get('test'));

        $questions = $request->get('answers');

        $correctAnswers = array();
        foreach ($test->getQuestions() as $key => $question) {
            $correctAnswers[]['answers'] = $this->getDoctrine()
                ->getRepository('App:Answer')
                ->findCorrectByQuestion($question);

        }
        return $this->json($correctAnswers);
    }
}

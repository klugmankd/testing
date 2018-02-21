<?php

namespace App\Controller;

use App\Entity\Test;
use App\Entity\UserPoints;
use App\Entity\UserResults;
use App\Service\CheckingManager;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    private $checkingManager;

    public function __construct(CheckingManager $checkingManager)
    {
        $this->checkingManager = $checkingManager;
    }

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
        $test->setOccurrence($request->get('occurrence'));
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
        $test->setOccurrence($request->get('occurrence'));
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

        $ids = $repository->findIds($direction, $difficulty);

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


    /**
     * @Route("/tests/check", name="tests_check")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkAction(Request $request)
    {
        $results = $this->checkingManager
            ->check($request);
        $parameters = $this->checkingManager
            ->getParameters($request);
        $presentResult = $this->getDoctrine()
            ->getRepository('App:UserResults')
            ->findOneBy([
                'user' => $parameters['user'],
                'test' => $parameters['test']
            ]);
        $userResult = (!is_null($presentResult)) ?
            $presentResult :
            new UserResults();

        $userResult->setUser($parameters['user']);
        $userResult->setTest($parameters['test']);
        $hasPassed = $parameters['test']->
            getBarrier() <= $results['userResult']['result'];
        $userResult->setHasPassed($hasPassed);
        $entityManager = $this->getDoctrine()
            ->getManager();
        $entityManager->persist($userResult);
        $entityManager->flush();
        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($results, 'json');
        return $this->json($jsonResponse);
    }

}

<?php

namespace App\Controller;

use App\Entity\Test;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

        return $this->json(
            $test
        );
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

        return $this->json(
            $tests
        );
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
}

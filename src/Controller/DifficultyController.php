<?php

namespace App\Controller;

use App\Entity\Difficulty;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DifficultyController extends Controller
{

    /**
     * @Route("/difficulties", name="difficulties_create")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request)
    {
        $difficulty = new Difficulty();
        $difficulty->setName($request->get('name'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($difficulty);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "create",
                'parameter' => "name",
                'value' => $difficulty->getName(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/difficulties/{id}", name="difficulties_get_one")
     * @Method({"GET"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function readAction($id)
    {
        $difficulty = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->find($id);

        return $this->json(
            $difficulty
        );
    }

    /**
     * @Route("/difficulties", name="difficulties_get_all")
     * @Method({"GET"})
     */
    public function readAllAction()
    {
        $difficulties = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->findAll();

        return $this->json($difficulties);
    }

    /**
     * @Route("/difficulties/{id}", name="difficulties_update")
     * @Method({"PUT"})
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction($id, Request $request)
    {
        $difficulty = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->find($id);

        $difficulty->setName($request->get('name'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($difficulty);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "update",
                'parameter' => "name",
                'value' => $difficulty->getName(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/difficulties/{id}", name="difficulties_delete")
     * @Method({"DELETE"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction($id)
    {
        $difficulty = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($difficulty);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "delete",
                'result' => 'success'
            )
        );
    }
}

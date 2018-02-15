<?php

namespace App\Controller;

use App\Entity\Direction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DirectionController extends Controller
{

    /**
     * @Route("/directions", name="directions_create")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request)
    {
        $direction = new Direction();
        $direction->setName($request->get('name'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($direction);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "create",
                'parameter' => "name",
                'value' => $direction->getName(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/directions/{id}", name="directions_get_one")
     * @Method({"GET"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function readAction($id)
    {
        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->find($id);

        return $this->json(
            $direction
        );
    }

    /**
     * @Route("/directions", name="directions_get_all")
     * @Method({"GET"})
     */
    public function readAllAction()
    {
        $directions = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findAll();

        return $this->json($directions);
    }

    /**
     * @Route("/directions/{id}", name="directions_update")
     * @Method({"PUT"})
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction($id, Request $request)
    {
        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->find($id);

        $direction->setName($request->get('name'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($direction);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "update",
                'parameter' => "name",
                'value' => $direction->getName(),
                'result' => 'success'
            )
        );
    }

    /**
     * @Route("/directions/{id}", name="directions_delete")
     * @Method({"DELETE"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction($id)
    {
        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($direction);
        $entityManager->flush();
        return $this->json(
            array(
                'action' => "delete",
                'result' => 'success'
            )
        );
    }
}

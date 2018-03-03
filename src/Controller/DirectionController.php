<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DirectionController
 * @package App\Controller
 * @Security("has_role('ROLE_USER')")
 */
class DirectionController extends Controller
{

    /**
     * @Route("/api/directions", name="api_directions")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function readActionAll()
    {
        $directions = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findAll();

        return $this->json($directions);
    }

    /**
     * @Route("/api/directions/{name}", name="api_difficulties")
     * @param $name
     * @return Response
     */
    public function readAction($name)
    {
        $user = $this->getUser();

        $direction = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findOneBy(['name' => $name]);

        $userPoints = $this->getDoctrine()
            ->getRepository('App:UserPoints')
            ->findOneBy([
                'direction' => $direction,
                'user' => $user
            ]);
        $currentLevel = 1;
        if (!is_null($userPoints)) {
            $currentLevel = $userPoints->getCurrentLevel();
        }
        $difficulties = $this->getDoctrine()
            ->getRepository('App:Difficulty')
            ->findByCurrentLevel($currentLevel);

        return $this->json([
            "difficulties" => $difficulties,
            "direction" => $name
        ]);
    }

}

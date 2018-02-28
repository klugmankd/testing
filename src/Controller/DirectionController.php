<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DirectionController extends Controller
{

    /**
     * @Route("/api/directions", name="app_directions")
     */
    public function readActionAll()
    {
        $directions = $this->getDoctrine()
            ->getRepository('App:Direction')
            ->findAll();

        return $this->json($directions);
    }

    /**
     * @Route("/api/directions/{name}", name="app_difficulties")
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

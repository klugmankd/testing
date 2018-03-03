<?php

namespace App\Service;


use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\Common\Persistence\ManagerRegistry;

class TokenManager
{
    private $doctrine;
    private $user;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function generateToken(User $user)
    {
        $this->user = $user;

        $header = json_encode(array("typ" => "JWT", "alg" => "base64"));
        $header = base64_encode($header);
        $payload = $user;
        $payload = base64_encode($payload);
        $secret = rand(100, 200) . rand(200, 400) . rand(400, 800);
        $secret = base64_encode($secret);
        $token = $header . "." . $payload . "." . $secret;

        return $token;
    }

    public function save($token)
    {
        $userToken = new UserToken();
        $userToken->setUser($this->user);
        $userToken->setToken($token);
        $entityManager = $this->doctrine
            ->getManager();
        $entityManager->persist($userToken);
        $entityManager->flush();

        return $userToken;
    }
}
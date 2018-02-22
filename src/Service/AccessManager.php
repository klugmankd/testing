<?php

namespace App\Service;


use Doctrine\Common\Persistence\ManagerRegistry;

class AccessManager
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }


}
<?php

namespace App\Repository;

use App\Entity\UserQuestions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserQuestionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserQuestions::class);
    }


    public function findByWasPassed($wasPassed)
    {
        return $this->createQueryBuilder('u')
            ->where('u.wasPassed = :wasPassed')
            ->setParameter('wasPassed', $wasPassed)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

}

<?php

namespace App\Repository;

use App\Entity\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Test::class);
    }


    public function findIds($direction, $difficulty)
    {
        return $this->createQueryBuilder('t')
            ->select('t.id')
            ->where('t.direction = :direction')
            ->andWhere('t.difficulty = :difficulty')
            ->setParameter('direction', $direction)
            ->setParameter('difficulty', $difficulty)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAccessible($direction, $userPoints)
    {
        return $this->createQueryBuilder('t')
            ->where('t.direction = :direction')
            ->andWhere('t.occurrence <= :points')
            ->setParameter('direction', $direction)
            ->setParameter('points', $userPoints)
            ->orderBy('t.difficulty', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }
}

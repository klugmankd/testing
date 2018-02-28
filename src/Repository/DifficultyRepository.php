<?php

namespace App\Repository;

use App\Entity\Difficulty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DifficultyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Difficulty::class);
    }


    public function findByCurrentLevel($currentLevel)
    {
        return $this->createQueryBuilder('d')
            ->where('d.level <= :currentLevel')
            ->setParameter('currentLevel', $currentLevel)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByNextLevel($currentLevel)
    {
        return $this->createQueryBuilder('d')
            ->where('d.level > :currentLevel')
            ->setParameter('currentLevel', $currentLevel)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

}

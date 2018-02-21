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


    public function findAccessible($id)
    {
        return $this->createQueryBuilder('d')
            ->where('d.id <= :id')
            ->setParameter('id', $id)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

}

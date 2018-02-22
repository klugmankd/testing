<?php

namespace App\Repository;

use App\Entity\UserResults;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserResultsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserResults::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('u')
            ->where('u.something = :value')->setParameter('value', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findByUser($user, $direction, $hasPassed)
    {
        return $this->createQueryBuilder('ur')
            ->leftJoin('ur.test', 'test')
            ->where('ur.user = :user')
            ->andWhere('test.direction = :direction')
            ->andWhere('ur.hasPassed = :hasPassed')
            ->setParameter('user', $user)
            ->setParameter('direction', $direction)
            ->setParameter('hasPassed', $hasPassed)
            ->getQuery()
            ->getResult()
            ;
    }
}

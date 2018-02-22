<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function findCorrectAnswers($test)
    {
        return $this->createQueryBuilder('q')
            ->select(['q', 'a'])
            ->leftJoin('q.answers', 'a')
            ->where('q.test = :test')
            ->andWhere('a.isCorrect = :isCorrect')
            ->setParameter('test', $test)
            ->setParameter('isCorrect', true)
            ->getQuery()
            ->getResult()
            ;
    }
}

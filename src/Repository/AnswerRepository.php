<?php

namespace App\Repository;

use App\Entity\Answer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Answer::class);
    }


    public function findCorrectByQuestion($question)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('a.question = :question')
            ->andWhere('a.isCorrect = :isCorrect')
            ->setParameter('question', $question)
            ->setParameter('isCorrect', 1)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

}

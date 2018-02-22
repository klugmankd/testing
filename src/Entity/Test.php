<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="`test`")
 * @ORM\Entity(repositoryClass="App\Repository\TestRepository")
 */
class Test
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Difficulty")
     * @ORM\JoinColumn(name="difficulty_id", referencedColumnName="id")
     */
    private $difficulty;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Direction")
     * @ORM\JoinColumn(name="direction_id", referencedColumnName="id")
     */
    private $direction;

    /**
     * @ORM\Column(type="integer")
     */
    private $barrier;

    /**
     * @ORM\Column(type="integer")
     */
    private $occurrence;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="test")
     */
    private $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * @param mixed $difficulty
     */
    public function setDifficulty($difficulty): void
    {
        $this->difficulty = $difficulty;
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param mixed $direction
     */
    public function setDirection($direction): void
    {
        $this->direction = $direction;
    }

    /**
     * @return mixed
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param mixed $questions
     */
    public function setQuestions($questions): void
    {
        $this->questions = $questions;
    }

    /**
     * @return mixed
     */
    public function getBarrier()
    {
        return $this->barrier;
    }

    /**
     * @param mixed $barrier
     */
    public function setBarrier($barrier): void
    {
        $this->barrier = $barrier;
    }

    /**
     * @return mixed
     */
    public function getOccurrence()
    {
        return $this->occurrence;
    }

    /**
     * @param mixed $occurrence
     */
    public function setOccurrence($occurrence): void
    {
        $this->occurrence = $occurrence;
    }
}

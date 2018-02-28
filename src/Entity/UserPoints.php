<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserPointsRepository")
 */
class UserPoints
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Direction")
     * @ORM\JoinColumn(name="direction_id", referencedColumnName="id")
     */
    private $direction;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Difficulty")
     * @ORM\JoinColumn(name="current_level_id", referencedColumnName="id")
     */
    private $currentLevel;

    /**
     * @ORM\Column(type="float")
     */
    private $points;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
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
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param mixed $points
     */
    public function setPoints($points = 0): void
    {
        $this->points = $points;
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
    public function getCurrentLevel()
    {
        return $this->currentLevel;
    }

    /**
     * @param mixed $currentLevel
     */
    public function setCurrentLevel($currentLevel): void
    {
        $this->currentLevel = $currentLevel;
    }
}
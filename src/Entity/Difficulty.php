<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DifficultyRepository")
 */
class Difficulty
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var boolean $hasPassed
     */
    private $hasPassed;

    /**
     * @var boolean $didNotPass
     */
    private $didNotPass;

    /**
     * @var boolean $accessible
     */
    private $accessible;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function hasPassed(): bool
    {
        return $this->hasPassed;
    }

    /**
     * @param bool $hasPassed
     */
    public function setHasPassed(bool $hasPassed): void
    {
        $this->hasPassed = $hasPassed;
    }

    /**
     * @return bool
     */
    public function didNotPass(): bool
    {
        return $this->didNotPass;
    }

    /**
     * @param bool $didNotPass
     */
    public function setDidNotPass(bool $didNotPass): void
    {
        $this->didNotPass = $didNotPass;
    }

    /**
     * @return bool
     */
    public function isAccessible(): bool
    {
        return $this->accessible;
    }

    /**
     * @param bool $accessible
     */
    public function setAccessible(bool $accessible): void
    {
        $this->accessible = $accessible;
    }
}

<?php

namespace VyatkinaA\ElectricBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Users
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="VyatkinaA\ElectricBundle\Repository\UsersRepository")
 */
class Users
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=500)
     */
    private $username;

    /**
     * @ORM\OneToMany(targetEntity="VyatkinaA\ElectricBundle\Entity\Results", mappedBy="userId")
     */
    private $results;

    public function __construct()
    {
        $this->results = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Users
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
}


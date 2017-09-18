<?php

namespace VyatkinaA\ElectricBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Steps
 *
 * @ORM\Table(name="steps")
 * @ORM\Entity(repositoryClass="VyatkinaA\ElectricBundle\Repository\StepsRepository")
 */
class Steps
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
     * @var array
     *
     * @ORM\Column(name="fields_on", type="array")
     */
    private $fields_on;

    /**
     * @var int
     *
     * @ORM\Column(name="step", type="integer")
     */
    private $step;


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
     * Set step
     *
     * @param integer $step
     *
     * @return Steps
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return integer
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @return array
     */
    public function getFieldsOn()
    {
        return $this->fields_on;
    }

    /**
     * @param array $fields_on
     */
    public function setFieldsOn($fields_on)
    {
        $this->fields_on = $fields_on;
    }
}


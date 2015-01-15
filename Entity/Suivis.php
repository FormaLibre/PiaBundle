<?php

namespace Laurent\PiaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="laurent_pia_suivi")
 */
class Suivis
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     */
    private $intervenant;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Laurent\PiaBundle\Entity\Taches",
     *     cascade={"persist"}
     * )
     */
    private $taches;

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $intervenant
     */
    public function setIntervenant($intervenant)
    {
        $this->intervenant = $intervenant;
    }

    /**
     * @return mixed
     */
    public function getIntervenant()
    {
        return $this->intervenant;
    }

    /**
     * @param mixed $taches
     */
    public function setTaches($taches)
    {
        $this->taches = $taches;
    }

    /**
     * @return mixed
     */
    public function getTaches()
    {
        return $this->taches;
    }



}
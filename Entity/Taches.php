<?php

namespace Laurent\PiaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="laurent_pia_taches")
 */
class Taches
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Laurent\PiaBundle\Entity\Actions",
     *     cascade={"persist"}
     * )
     */
    private $action;

    /**
     * @ORM\Column(nullable=true)
     */
    private $titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     */
    private $responsable;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     */
    private $eleves;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     * @ORM\joinTable(name="laurent_pia_taches_user")
     */
    private $intervenant;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $fini;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $priorite;

    /**
     * @param mixed $commentaire
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;
    }

    /**
     * @return mixed
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * @param mixed $fini
     */
    public function setFini($fini)
    {
        $this->fini = $fini;
    }

    /**
     * @return mixed
     */
    public function getFini()
    {
        return $this->fini;
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
     * @param mixed $priorite
     */
    public function setPriorite($priorite)
    {
        $this->priorite = $priorite;
    }

    /**
     * @return mixed
     */
    public function getPriorite()
    {
        return $this->priorite;
    }

    /**
     * @param mixed $responsable
     */
    public function setResponsable($responsable)
    {
        $this->responsable = $responsable;
    }

    /**
     * @return mixed
     */
    public function getResponsable()
    {
        return $this->responsable;
    }

    /**
     * @param mixed $titre
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    /**
     * @return mixed
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * @param mixed $eleves
     */
    public function setEleves($eleves)
    {
        $this->eleves = $eleves;
    }

    /**
     * @return mixed
     */
    public function getEleves()
    {
        return $this->eleves;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }



}
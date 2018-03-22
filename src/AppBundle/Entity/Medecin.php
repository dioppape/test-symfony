<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Medecin
 */
class Medecin
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var string
     */
    private $prenom;

    /**
     * @var string
     */
    private $rpps;


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $patientes;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Medecin
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     * @return Medecin
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set rpps
     *
     * @param string $rpps
     * @return Medecin
     */
    public function setRpps($rpps)
    {
        $this->rpps = $rpps;

        return $this;
    }

    /**
     * Get rpps
     *
     * @return string 
     */
    public function getRpps()
    {
        return $this->rpps;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patientes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add patientes
     *
     * @param \AppBundle\Entity\Patient $patientes
     * @return Medecin
     */
    public function addPatiente(\AppBundle\Entity\Patient $patientes)
    {
        $this->patientes[] = $patientes;

        return $this;
    }

    /**
     * Remove patientes
     *
     * @param \AppBundle\Entity\Patient $patientes
     */
    public function removePatiente(\AppBundle\Entity\Patient $patientes)
    {
        $this->patientes->removeElement($patientes);
    }

    /**
     * Get patientes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPatientes()
    {
        return $this->patientes;
    }
}

<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\PaiementsRepository") 
 */
class Paiements {

    const PREFIX = "PAIEMENTS";

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\PaiementsGenerator"})
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $identifiant;

    /**
     * @MongoDB\Date
     */
    protected $dateCreation;

    /**
     * @MongoDB\EmbedMany(targetDocument="Paiement", strategy="set")
     */
    protected $paiement;

    /**
     * @MongoDB\Boolean
     */
    protected $imprime;

    public function __construct()
    {
        $this->paiement = new ArrayCollection();
        $this->imprime = false;
        
    }
    
    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set identifiant
     *
     * @param string $identifiant
     * @return self
     */
    public function setIdentifiant($identifiant)
    {
        $this->identifiant = $identifiant;
        return $this;
    }

    /**
     * Get identifiant
     *
     * @return string $identifiant
     */
    public function getIdentifiant()
    {
        return $this->identifiant;
    }

    /**
     * Set dateCreation
     *
     * @param date $dateCreation
     * @return self
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return date $dateCreation
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Add paiement
     *
     * @param AppBundle\Document\Paiement $paiement
     */
    public function addPaiement(\AppBundle\Document\Paiement $paiement)
    {
        $this->paiement[] = $paiement;
    }

    /**
     * Remove paiement
     *
     * @param AppBundle\Document\Paiement $paiement
     */
    public function removePaiement(\AppBundle\Document\Paiement $paiement)
    {
        $this->paiement->removeElement($paiement);
    }

    /**
     * Get paiement
     *
     * @return \Doctrine\Common\Collections\Collection $paiement
     */
    public function getPaiement()
    {
        return $this->paiement;
    }

    /**
     * Set imprime
     *
     * @param boolean $imprime
     * @return self
     */
    public function setImprime($imprime)
    {
        $this->imprime = $imprime;
        return $this;
    }

    /**
     * Get imprime
     *
     * @return boolean $imprime
     */
    public function getImprime()
    {
        return $this->imprime;
    }
    
    public function getMontantTotal() {
        $montantTotal = 0;
        foreach ($this->getPaiement() as $paiement) {
            $montantTotal+=$paiement->getMontant();
        }
        return $montantTotal;
    }
    
    public function getPaiementBySociete($societe) {
        $paiementBySoc = array();
        foreach ($this->getPaiement() as $paiement) {
            if($paiement->getFacture()->getSociete() == $societe){
                $paiementBySoc[] =$paiement;
            }
        }
        return $paiementBySoc;
    }
    
    public function getTotalBySociete($societe) {
        $montantTotal = 0;
        foreach ($this->getPaiementBySociete($societe) as $paiement) {
            $montantTotal+=$paiement->getMontant();
        }
        return $montantTotal;
    }    
    
}

<?php
namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Document\Etablissement;
use AppBundle\Document\User;
use AppBundle\Document\Prestation;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\ContratRepository") @HasLifecycleCallbacks
 * 
 */
class Contrat {

    const PREFIX = "CONTRAT";
    const STATUT_BROUILLON = "BROUILLON";
    const STATUT_VALIDE = "VALIDE";

    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Etablissement", inversedBy="contrats")
     */
    protected $etablissement;

    /**
     * @MongoDB\ReferenceOne(targetDocument="User")
     */
    protected $commercial;
    
    /**
     * @MongoDB\ReferenceOne(targetDocument="User")
     */
    protected $technicien;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Passage")
     */
    protected $passages;

    
    /**
     * @MongoDB\String
     */
    protected $identifiant;

    /**
     * @MongoDB\String
     */
    protected $typeContrat;

     /**
     * @MongoDB\String
     */
    protected $localisation;

    /**
     * @MongoDB\EmbedMany(targetDocument="Prestation")
     */
    protected $prestations;

    /**
     * @MongoDB\Date
     */
    protected $dateCreation;

    /**
     * @MongoDB\Date
     */
    protected $dateDebut;

    /**
     * @MongoDB\Int
     */
    protected $duree;

    /**
     * @MongoDB\Int
     */
    protected $dureeGarantie;

    /**
     * @MongoDB\Int
     */
    protected $nbPassage;

    /**
     * @MongoDB\Int
     */
    protected $dureePassage;

    /**
     * @MongoDB\Int
     */
    protected $frequenceFacturation;

    /**
     * @MongoDB\String
     */
    protected $typeFacturation;    

    /**
     * @MongoDB\Float
     */
    protected $prixHt;

    /**
     * @MongoDB\String
     */
    protected $statut;  

  
    public function __construct()
    {
        $this->prestations = new ArrayCollection();
        $this->passages = new ArrayCollection();
    }
    
    /**
     * Set id
     *
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * Generate id
     *
     * @return self
     */
    public function generateId()
    {
    	return $this->setId(self::PREFIX . '-' . $this->identifiant);
    }

    /**
     * Set etablissement
     *
     * @param Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Get etablissement
     *
     * @return Etablissement $etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Set commercial
     *
     * @param User $commercial
     * @return self
     */
    public function setCommercial(User $commercial)
    {
        $this->commercial = $commercial;
        return $this;
    }

    /**
     * Get commercial
     *
     * @return User $commercial
     */
    public function getCommercial()
    {
        return $this->commercial;
    }

    /**
     * Set technicien
     *
     * @param User $technicien
     * @return self
     */
    public function setTechnicien(User $technicien)
    {
        $this->technicien = $technicien;
        return $this;
    }

    /**
     * Get technicien
     *
     * @return User $technicien
     */
    public function getTechnicien()
    {
        return $this->technicien;
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
     * Set typeContrat
     *
     * @param string $typeContrat
     * @return self
     */
    public function setTypeContrat($typeContrat)
    {
        $this->typeContrat = $typeContrat;
        return $this;
    }

    /**
     * Get typeContrat
     *
     * @return string $typeContrat
     */
    public function getTypeContrat()
    {
        return $this->typeContrat;
    }

    /**
     * Add prestation
     *
     * @param Prestation $prestation
     */
    public function addPrestation(Prestation $prestation)
    {
        $this->prestations[] = $prestation;
    }

    /**
     * Remove prestation
     *
     * @param Prestation $prestation
     */
    public function removePrestation(Prestation $prestation)
    {
        $this->prestations->removeElement($prestation);
    }

    /**
     * Get prestations
     *
     * @return \Doctrine\Common\Collections\Collection $prestations
     */
    public function getPrestations()
    {
        return $this->prestations;
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
     * Set dateDebut
     *
     * @param date $dateDebut
     * @return self
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return date $dateDebut
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set duree
     *
     * @param int $duree
     * @return self
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;
        return $this;
    }

    /**
     * Get duree
     *
     * @return int $duree
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set dureeGarantie
     *
     * @param int $dureeGarantie
     * @return self
     */
    public function setDureeGarantie($dureeGarantie)
    {
        $this->dureeGarantie = $dureeGarantie;
        return $this;
    }

    /**
     * Get dureeGarantie
     *
     * @return int $dureeGarantie
     */
    public function getDureeGarantie()
    {
        return $this->dureeGarantie;
    }

    /**
     * Set nbPassage
     *
     * @param int $nbPassage
     * @return self
     */
    public function setNbPassage($nbPassage)
    {
        $this->nbPassage = $nbPassage;
        return $this;
    }

    /**
     * Get nbPassage
     *
     * @return int $nbPassage
     */
    public function getNbPassage()
    {
        return $this->nbPassage;
    }

    /**
     * Set dureePassage
     *
     * @param int $dureePassage
     * @return self
     */
    public function setDureePassage($dureePassage)
    {
        $this->dureePassage = $dureePassage;
        return $this;
    }

    /**
     * Get dureePassage
     *
     * @return int $dureePassage
     */
    public function getDureePassage()
    {
        return $this->dureePassage;
    }

    /**
     * Set frequenceFacturation
     *
     * @param int $frequenceFacturation
     * @return self
     */
    public function setFrequenceFacturation($frequenceFacturation)
    {
        $this->frequenceFacturation = $frequenceFacturation;
        return $this;
    }

    /**
     * Get frequenceFacturation
     *
     * @return int $frequenceFacturation
     */
    public function getFrequenceFacturation()
    {
        return $this->frequenceFacturation;
    }

    /**
     * Set typeFacturation
     *
     * @param string $typeFacturation
     * @return self
     */
    public function setTypeFacturation($typeFacturation)
    {
        $this->typeFacturation = $typeFacturation;
        return $this;
    }

    /**
     * Get typeFacturation
     *
     * @return string $typeFacturation
     */
    public function getTypeFacturation()
    {
        return $this->typeFacturation;
    }

    /**
     * Set prixHt
     *
     * @param float $prixHt
     * @return self
     */
    public function setPrixHt($prixHt)
    {
        $this->prixHt = $prixHt;
        return $this;
    }

    /**
     * Get prixHt
     *
     * @return float $prixHt
     */
    public function getPrixHt()
    {
        return $this->prixHt;
    }

    /**
     * Set statut
     *
     * @param string $statut
     * @return self
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;
        return $this;
    }
    
    /**
     * Add passage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function addPassage(\AppBundle\Document\Passage $passage)
    {
        $this->passages[] = $passage;
    }

    /**
     * Remove passage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function removePassage(\AppBundle\Document\Passage $passage)
    {
        $this->passages->removeElement($passage);
    }

    /**
     * Get passages
     *
     * @return \Doctrine\Common\Collections\Collection $passages
     */
    public function getPassages()
    {
        return $this->passages;
    }

    /**
     * Set localisation
     *
     * @param string $localisation
     * @return self
     */
    public function setLocalisation($localisation)
    {
        $this->localisation = $localisation;
        return $this;
    }

    /**
     * Get localisation
     *
     * @return string $localisation
     */
    public function getLocalisation()
    {
        return $this->localisation;
    }

    /**
     * Get statut
     *
     * @return string $statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    public function getDateFin() {

        $dateFin = clone $this->getDateDebut();
        $dateFin->modify("+ ".$this->getDuree()." months");

        return $dateFin;
    }

    public function isTerminee() {

        return ($this->getDateFin() < new \DateTime());
    }

    public function getNbPassagePrevu() {
        foreach($this->getPassages() as $passage) {
            if(preg_match("/Passage[n° ]*[0-9]+ sur ([0-9]+)/i", $passage->getLibelle(), $matches)) {

                return $matches[1];
            }
        }

        return 1;
    }
}

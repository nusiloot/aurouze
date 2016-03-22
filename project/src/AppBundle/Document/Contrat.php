<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Document\Etablissement;
use AppBundle\Document\User;
use AppBundle\Document\Prestation;
use AppBundle\Document\Passage;
use AppBundle\Document\Intervention;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\ContratRepository") @HasLifecycleCallbacks
 * 
 */
class Contrat {

    const PREFIX = "CONTRAT";

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
    protected $nomenclature;

    /**
     * @MongoDB\Hash
     */
    protected $prestations;

    /**
     * @MongoDB\EmbedMany(targetDocument="Intervention")
     */
    protected $interventions;

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

    public function __construct() {
        $this->prestations = array();
        $this->passages = new ArrayCollection();
        $this->interventions = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param string $id
     * @return self
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Generate id
     *
     * @return self
     */
    public function generateId() {
        return $this->setId(self::PREFIX . '-' . $this->identifiant);
    }

    /**
     * Set etablissement
     *
     * @param Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement) {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Get etablissement
     *
     * @return Etablissement $etablissement
     */
    public function getEtablissement() {
        return $this->etablissement;
    }

    /**
     * Set commercial
     *
     * @param User $commercial
     * @return self
     */
    public function setCommercial(User $commercial) {
        $this->commercial = $commercial;
        return $this;
    }

    /**
     * Get commercial
     *
     * @return User $commercial
     */
    public function getCommercial() {
        return $this->commercial;
    }

    /**
     * Set technicien
     *
     * @param User $technicien
     * @return self
     */
    public function setTechnicien(User $technicien) {
        $this->technicien = $technicien;
        return $this;
    }

    /**
     * Get technicien
     *
     * @return User $technicien
     */
    public function getTechnicien() {
        return $this->technicien;
    }

    /**
     * Set identifiant
     *
     * @param string $identifiant
     * @return self
     */
    public function setIdentifiant($identifiant) {
        $this->identifiant = $identifiant;
        return $this;
    }

    /**
     * Get identifiant
     *
     * @return string $identifiant
     */
    public function getIdentifiant() {
        return $this->identifiant;
    }

    /**
     * Set typeContrat
     *
     * @param string $typeContrat
     * @return self
     */
    public function setTypeContrat($typeContrat) {
        $this->typeContrat = $typeContrat;
        return $this;
    }

    /**
     * Get typeContrat
     *
     * @return string $typeContrat
     */
    public function getTypeContrat() {
        return $this->typeContrat;
    }

    /**
     * Get prestations
     *
     * @return collection $prestations
     */
    public function getPrestations() {
        return $this->prestations;
    }
    
    /**
     * Add intervention
     *
     * @param Intervention $intervention
     */
    public function addIntervention(Intervention $intervention) {
    	$this->interventions[] = $intervention;
    }
    
    /**
     * Remove intervention
     *
     * @param Intervention $intervention
     */
    public function removeIntervention(Intervention $intervention) {
    	$this->interventions->removeElement($intervention);
    }
    
    /**
     * Get interventions
     *
     * @return \Doctrine\Common\Collections\Collection $interventions
     */
    public function getInterventions() {
    	return $this->interventions;
    }

    /**
     * Set dateCreation
     *
     * @param date $dateCreation
     * @return self
     */
    public function setDateCreation($dateCreation) {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return date $dateCreation
     */
    public function getDateCreation() {
        return $this->dateCreation;
    }

    /**
     * Set dateDebut
     *
     * @param date $dateDebut
     * @return self
     */
    public function setDateDebut($dateDebut) {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return date $dateDebut
     */
    public function getDateDebut() {
        return $this->dateDebut;
    }

    /**
     * Set duree
     *
     * @param int $duree
     * @return self
     */
    public function setDuree($duree) {
        $this->duree = $duree;
        return $this;
    }

    /**
     * Get duree
     *
     * @return int $duree
     */
    public function getDuree() {
        return $this->duree;
    }

    /**
     * Set dureeGarantie
     *
     * @param int $dureeGarantie
     * @return self
     */
    public function setDureeGarantie($dureeGarantie) {
        $this->dureeGarantie = $dureeGarantie;
        return $this;
    }

    /**
     * Get dureeGarantie
     *
     * @return int $dureeGarantie
     */
    public function getDureeGarantie() {
        return $this->dureeGarantie;
    }

    /**
     * Set nbPassage
     *
     * @param int $nbPassage
     * @return self
     */
    public function setNbPassage($nbPassage) {
        $this->nbPassage = $nbPassage;
        return $this;
    }

    /**
     * Get nbPassage
     *
     * @return int $nbPassage
     */
    public function getNbPassage() {
        return $this->nbPassage;
    }

    /**
     * Set dureePassage
     *
     * @param int $dureePassage
     * @return self
     */
    public function setDureePassage($dureePassage) {
        $this->dureePassage = $dureePassage;
        return $this;
    }

    /**
     * Get dureePassage
     *
     * @return int $dureePassage
     */
    public function getDureePassage() {
        return $this->dureePassage;
    }

    /**
     * Set frequenceFacturation
     *
     * @param int $frequenceFacturation
     * @return self
     */
    public function setFrequenceFacturation($frequenceFacturation) {
        $this->frequenceFacturation = $frequenceFacturation;
        return $this;
    }

    /**
     * Get frequenceFacturation
     *
     * @return int $frequenceFacturation
     */
    public function getFrequenceFacturation() {
        return $this->frequenceFacturation;
    }

    /**
     * Set typeFacturation
     *
     * @param string $typeFacturation
     * @return self
     */
    public function setTypeFacturation($typeFacturation) {
        $this->typeFacturation = $typeFacturation;
        return $this;
    }

    /**
     * Get typeFacturation
     *
     * @return string $typeFacturation
     */
    public function getTypeFacturation() {
        return $this->typeFacturation;
    }

    /**
     * Set prixHt
     *
     * @param float $prixHt
     * @return self
     */
    public function setPrixHt($prixHt) {
        $this->prixHt = $prixHt;
        return $this;
    }

    /**
     * Get prixHt
     *
     * @return float $prixHt
     */
    public function getPrixHt() {
        return $this->prixHt;
    }

    /**
     * Set statut
     *
     * @param string $statut
     * @return self
     */
    public function setStatut($statut) {
        $this->statut = $statut;
        return $this;
    }

    /**
     * Add passage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function addPassage(\AppBundle\Document\Passage $passage) {
        $this->passages[] = $passage;
    }

    /**
     * Remove passage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function removePassage(\AppBundle\Document\Passage $passage) {
        $this->passages->removeElement($passage);
    }

    /**
     * Get passages
     *
     * @return \Doctrine\Common\Collections\Collection $passages
     */
    public function getPassages() {
        return $this->passages;
    }

    

    /**
     * Get statut
     *
     * @return string $statut
     */
    public function getStatut() {
        return $this->statut;
    }

    public function getDateFin() {

        $dateFin = clone $this->getDateDebut();
        $dateFin->modify("+ " . $this->getDuree() . " month");

        return $dateFin;
    }

    public function isTerminee() {

        return ($this->getDateFin() < new \DateTime());
    }

    public function getNextPassage() {
        if ((count($this->getPassages()) < $this->nbPassage) && $this->getDateNextPassage()) {
            $passage = new Passage();
            $passage->setEtablissementIdentifiant($this->getEtablissement()->getIdentifiant());
            $passage->setEtablissementId($this->getEtablissement()->getId());
            $passage->setDateCreation(new \DateTime());
            $passage->setDateDebut($this->getDateNextPassage());
            $passage->getEtablissementInfos()->pull($this->getEtablissement());
            $passage->setNumeroPassageIdentifiant("001");
            $passage->generateId();
            $passage->setContratId($this->id);
            return $passage;
        }
        return null;
    }

    public function getDateNextPassage() {

        $nbPassage = $this->getNbPassage();
        if ($nbPassage >= 1 && !count($this->getPassages())) {
            return $this->getDateDebut();
        }
      
        if (!count($this->getLastPassageTermine()) && $this->getLastPassageTermine()) {
            return null;
        }        
        
        $dateDebutDernierPassage = clone $this->getLastPassageTermine()->getDateDebut();
        
        $monthInterval = (floatval($this->getDuree()) / floatval($nbPassage));
        $nb_month = intval($monthInterval);
        
        $monthDate = clone $this->getLastPassageTermine()->getDateDebut();
        $nextMonth = $monthDate->modify("+" . $nb_month . " month");
        $nb_days = intval(($monthInterval - $nb_month) * cal_days_in_month(CAL_GREGORIAN,$nextMonth->format('m'),$nextMonth->format('Y')));       
        $dateDebutDernierPassage->modify("+" . $nb_month . " month")->modify("+" . $nb_days . " day");        
        return $dateDebutDernierPassage;
    }

    public function getLastPassageTermine() {
        $passages = array();
        foreach ($this->getPassages() as $passage) {
            if ($passage->getDateFin()) {
                $passages[$passage->getDateFin()->format('Ymd')] = $passage;
            }
        } 
        return end($passages);
    }

    public function getNbPassagePrevu() {
        if($this->getNbPassage()){
            return $this->getNbPassage();
        }
        foreach ($this->getPassages() as $passage) {
            if (preg_match("/Passage[n° ]*[0-9]+ sur ([0-9]+)/i", $passage->getLibelle(), $matches)) {

                return $matches[1];
            }
        }

        return 1;
    }

    public function getPassagesSorted() {
        $passagesSorted = array();

        foreach ($this->getPassages() as $passage) {
            $passagesSorted[$passage->getId()] = $passage;
        }

        krsort($passagesSorted);

        return $passagesSorted;
    }


    /**
     * Set nomenclature
     *
     * @param string $nomenclature
     * @return self
     */
    public function setNomenclature($nomenclature)
    {
        $this->nomenclature = $nomenclature;
        return $this;
    }

    /**
     * Get nomenclature
     *
     * @return string $nomenclature
     */
    public function getNomenclature()
    {
        return $this->nomenclature;
    }
    
    public function generateInterventions()
    {
        $this->interventions = new ArrayCollection();
    	if ($nbPassage = $this->getNbPassage()) {
    		for ($i=0; $i<$nbPassage; $i++) {
    			$intervention = new Intervention();
    			$intervention->setFacturable(false);
    			$intervention->setPrestations($this->getPrestations());
    			$this->addIntervention($intervention);
    		}
    	}
    }

    /**
     * Set prestations
     *
     * @param collection $prestations
     * @return self
     */
    public function setPrestations($prestations)
    {
        $this->prestations = $prestations;
        return $this;
    }
}

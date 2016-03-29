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
use AppBundle\Document\Mouvement;

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
     * @MongoDB\Date
     */
    protected $dateFin;

    /**
     * @MongoDB\Date
     */
    protected $dateAcceptation;

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
    protected $nbPassages;

    /**
     * @MongoDB\Int
     */
    protected $dureePassage;

    /**
     * @MongoDB\Int
     */
    protected $nbFactures;

    /**
     * @MongoDB\Float
     */
    protected $prixHt;

    /**
     * @MongoDB\EmbedMany(targetDocument="Mouvement")
     */
    protected $mouvements;

    /**
     * @MongoDB\String
     */
    protected $statut;

    public function __construct() {
        $this->passages = new ArrayCollection();
        $this->prestations = new ArrayCollection();
        $this->mouvements = new ArrayCollection();
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
     * Set etablissement
     *
     * @param AppBundle\Document\Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(\AppBundle\Document\Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Get etablissement
     *
     * @return AppBundle\Document\Etablissement $etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Set commercial
     *
     * @param AppBundle\Document\User $commercial
     * @return self
     */
    public function setCommercial(\AppBundle\Document\User $commercial)
    {
        $this->commercial = $commercial;
        return $this;
    }

    /**
     * Get commercial
     *
     * @return AppBundle\Document\User $commercial
     */
    public function getCommercial()
    {
        return $this->commercial;
    }

    /**
     * Set technicien
     *
     * @param AppBundle\Document\User $technicien
     * @return self
     */
    public function setTechnicien(\AppBundle\Document\User $technicien)
    {
        $this->technicien = $technicien;
        return $this;
    }

    /**
     * Get technicien
     *
     * @return AppBundle\Document\User $technicien
     */
    public function getTechnicien()
    {
        return $this->technicien;
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

    /**
     * Add prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function addPrestation(\AppBundle\Document\Prestation $prestation)
    {
        $this->prestations[] = $prestation;
    }

    /**
     * Remove prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function removePrestation(\AppBundle\Document\Prestation $prestation)
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
     * Set dateAcceptation
     *
     * @param date $dateAcceptation
     * @return self
     */
    public function setDateAcceptation($dateAcceptation)
    {
        $this->dateAcceptation = $dateAcceptation;
        return $this;
    }

    /**
     * Get dateAcceptation
     *
     * @return date $dateAcceptation
     */
    public function getDateAcceptation()
    {
        return $this->dateAcceptation;
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
     * Set nbPassages
     *
     * @param int $nbPassages
     * @return self
     */
    public function setNbPassages($nbPassages)
    {
        $this->nbPassages = $nbPassages;
        return $this;
    }

    /**
     * Get nbPassages
     *
     * @return int $nbPassages
     */
    public function getNbPassages()
    {
        return $this->nbPassages;
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
     * Set nbFactures
     *
     * @param int $nbFactures
     * @return self
     */
    public function setNbFactures($nbFactures)
    {
        $this->nbFactures = $nbFactures;
        return $this;
    }

    /**
     * Get nbFactures
     *
     * @return int $nbFactures
     */
    public function getNbFactures()
    {
        return $this->nbFactures;
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
     * Get statut
     *
     * @return string $statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    public function isTerminee() {

        return ($this->getDateFin() < new \DateTime());
    }

    public function getNextPassage() {
        if ((count($this->getPassages()) < $this->nbPassages) && $this->getDateNextPassage()) {
            $passage = new Passage();
            $passage->setEtablissementIdentifiant($this->getEtablissement()->getIdentifiant());
            $passage->setEtablissementId($this->getEtablissement()->getId());
            $passage->setDatePrevision($this->getDateNextPassage());
            $passage->getEtablissementInfos()->pull($this->getEtablissement());
            $passage->setNumeroPassageIdentifiant("001");
            $passage->generateId();
            $passage->setContratId($this->id);
            return $passage;
        }
        return null;
    }

    public function getDateNextPassage() {

        $nbPassages = $this->getNbPassages();
        if ($nbPassages >= 1 && !count($this->getPassages())) {
            return $this->getDateDebut();
        }

        if (!count($this->getLastPassageCreated()) || !$this->getLastPassageCreated()) {
            return null;
        }

        $dateDebutDernierPassage = clone $this->getLastPassageCreated()->getDatePrevision();

        $monthInterval = (floatval($this->getDuree()) / floatval($nbPassages));

        $nb_month = intval($monthInterval);

        $monthDate = clone $this->getLastPassageCreated()->getDatePrevision();

        $nextMonth = $monthDate->modify("+" . $nb_month . " month");
        $nb_days = intval(($monthInterval - $nb_month) * cal_days_in_month(CAL_GREGORIAN,$nextMonth->format('m'),$nextMonth->format('Y')));
        $dateDebutDernierPassage->modify("+" . $nb_month . " month")->modify("+" . $nb_days . " day");
        return $dateDebutDernierPassage;
    }


    public function hasAllPassagesCreated() {
        return $this->getNbPassages() > count($this->getPassages());
    }

    public function getLastPassageCreated() {
        $passages = array();
        foreach ($this->getPassages() as $passage) {
            if ($passage->getDatePrevision()) {
                $passages[$passage->getDatePrevision()->format('Ymd')] = $passage;
            }
        }
        return end($passages);
    }

    public function getNbPassagePrevu() {
        if($this->getNbPassages()){
            return $this->getNbPassages();
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

    public function updateObject() {
    	if (!$this->getNbPassages()) {
    		$max = 0;
    		foreach ($this->getPrestations() as $prestation) {
    			if ($prestation->getNbPassages() > $max) {
    				$max = $prestation->getNbPassages();
    			}
    		}
    		$this->setNbPassages($max);
    	}
    }

    public function getHumanDureePassage() {
    	$duree = $this->getDureePassage();
    	$heure = floor($duree / 60);
    	return sprintf('%02d',$heure).'h'.sprintf('%02d',((($duree / 60) - $heure) * 60));
    }


    /**
     * Set dateFin
     *
     * @param date $dateFin
     * @return self
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * Get dateFin
     *
     * @return date $dateFin
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    public function getPrixMouvements() {
        $prix = 0;
        foreach($this->getMouvements() as $mouvement) {
            $prix = $prix + $mouvement->getPrix();
        }

        return $prix;
    }

    public function getPrixRestant() {
        $prixMouvement = $this->getPrixMouvements();

        return $this->getPrixHt() - $this->getPrixMouvements();
    }

    public function getNbFacturesRestantes() {

        return 1;
    }

    public function generateMouvement() {
        if($this->getPrixRestant() <= 0 || $this->getNbFacturesRestantes() <= 0) {
            return;
        }
        $mouvement = new Mouvement();
        $mouvement->setPrix(round($this->getPrixRestant() / $this->getNbFacturesRestantes(), 2));
        $mouvement->setFacturable(true);
        $mouvement->setFacture(false);
        $this->addMouvement($mouvement);
    }

    /**
     * Add mouvement
     *
     * @param AppBundle\Document\Mouvement $mouvement
     */
    public function addMouvement(\AppBundle\Document\Mouvement $mouvement)
    {
        $this->mouvements[] = $mouvement;
    }

    /**
     * Remove mouvement
     *
     * @param AppBundle\Document\Mouvement $mouvement
     */
    public function removeMouvement(\AppBundle\Document\Mouvement $mouvement)
    {
        $this->mouvements->removeElement($mouvement);
    }

    /**
     * Get mouvements
     *
     * @return \Doctrine\Common\Collections\Collection $mouvements
     */
    public function getMouvements()
    {
        return $this->mouvements;
    }
}

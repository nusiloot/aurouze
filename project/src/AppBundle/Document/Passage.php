<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Passages
 *
 * @author mathurin
 */

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\PassageRepository")
 */
class Passage {

    const PREFIX = "PASSAGE";

    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $etablissementIdentifiant;

    /**
     * @MongoDB\String
     */
    protected $identifiant;
    
    /**
     * @MongoDB\String
     */
    protected $prestationIdentifiant;

    /**
     * @MongoDB\String
     */
    protected $numeroPassageIdentifiant;

    /**
     * @MongoDB\String
     */
    protected $societeIdentifiant;

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
     * @MongoDB\EmbedOne(targetDocument="PassageEtablissement")
     */
    protected $passageEtablissement;

    /**
     * @MongoDB\String
     */
    protected $libelle;

    /**
     * @MongoDB\String
     */
    protected $description;

    /**
     * @MongoDB\String
     */
    protected $technicien;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set id
     *
     * @return id $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    public function generateId($fromImport = false) {
        if (!$this->getDateCreation()) {
            $this->setDateCreation(new \DateTime());
        }
        $this->identifiant = $this->dateCreation->format('Ymd') . '-' . $this->numeroPassageIdentifiant;
        return self::PREFIX . '-' . $this->etablissementIdentifiant . '-' . $this->identifiant;
    }

    /**
     * Set etablissementIdentifiant
     *
     * @param string $etablissementIdentifiant
     * @return self
     */
    public function setEtablissementIdentifiant($etablissementIdentifiant) {
        $this->etablissementIdentifiant = $etablissementIdentifiant;
        return $this;
    }

    /**
     * Get etablissementIdentifiant
     *
     * @return string $etablissementIdentifiant
     */
    public function getEtablissementIdentifiant() {
        return $this->etablissementIdentifiant;
    }

    /**
     * Set societeIdentifiant
     *
     * @param string $societeIdentifiant
     * @return self
     */
    public function setSocieteIdentifiant($societeIdentifiant) {
        $this->societeIdentifiant = $societeIdentifiant;
        return $this;
    }

    /**
     * Get societeIdentifiant
     *
     * @return string $societeIdentifiant
     */
    public function getSocieteIdentifiant() {
        return $this->societeIdentifiant;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return self
     */
    public function setTelephone($telephone) {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * Get telephone
     *
     * @return string $telephone
     */
    public function getTelephone() {
        return $this->telephone;
    }

    /**
     * Set prestationIdentifiant
     *
     * @param string $prestationIdentifiant
     * @return self
     */
    public function setPrestationIdentifiant($prestationIdentifiant) {
        $this->prestationIdentifiant = $prestationIdentifiant;
        return $this;
    }

    /**
     * Get prestationIdentifiant
     *
     * @return string $prestationIdentifiant
     */
    public function getPrestationIdentifiant() {
        return $this->prestationIdentifiant;
    }

    /**
     * Set numeroPassageIdentifiant
     *
     * @param string $numeroPassageIdentifiant
     * @return self
     */
    public function setNumeroPassageIdentifiant($numeroPassageIdentifiant) {
        $this->numeroPassageIdentifiant = $numeroPassageIdentifiant;
        return $this;
    }

    /**
     * Get numeroPassageIdentifiant
     *
     * @return string $numeroPassageIdentifiant
     */
    public function getNumeroPassageIdentifiant() {
        return $this->numeroPassageIdentifiant;
    }

    public function updateEtablissementInfos(Etablissement $etb) {
        $this->passageEtablissement = new PassageEtablissement();
        $this->passageEtablissement->setNom($etb->getNom());
        $this->passageEtablissement->setRaisonSociale($etb->getRaisonSociale());
        $this->passageEtablissement->setNomContact($etb->getNomContact());
        $this->passageEtablissement->setAdresse($etb->getAdresse()->getAdresse());
        $this->passageEtablissement->setCodePostal($etb->getAdresse()->getCodePostal());
        $this->passageEtablissement->setCommune($etb->getAdresse()->getCommune());
        $this->passageEtablissement->setTelephonePortable($etb->getAdresse()->getTelephonePortable());
        $this->passageEtablissement->setTelephoneFixe($etb->getAdresse()->getTelephoneFixe());
        $this->passageEtablissement->setTypeEtablissement($etb->getTypeEtablissement());
    }

    /**
     * Set etablissement
     *
     * @param AppBundle\Document\PassageEtablissement $etablissement
     * @return self
     */
    public function setEtablissement(\AppBundle\Document\PassageEtablissement $etablissement) {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Get etablissement
     *
     * @return AppBundle\Document\PassageEtablissement $etablissement
     */
    public function getEtablissement() {
        return $this->etablissement;
    }

    /**
     * Set passageEtablissement
     *
     * @param AppBundle\Document\PassageEtablissement $passageEtablissement
     * @return self
     */
    public function setPassageEtablissement(\AppBundle\Document\PassageEtablissement $passageEtablissement) {
        $this->passageEtablissement = $passageEtablissement;
        return $this;
    }

    /**
     * Get passageEtablissement
     *
     * @return AppBundle\Document\PassageEtablissement $passageEtablissement
     */
    public function getPassageEtablissement() {
        return $this->passageEtablissement;
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
     * Set dateFin
     *
     * @param date $dateFin
     * @return self
     */
    public function setDateFin($dateFin) {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * Get dateEffectue
     *
     * @return date $dateEffectue
     */
    public function getEffectue() {
        return $this->dateEffectue;
    }

    /**
     * Set dateEffectue
     *
     * @param date dateEffectue
     * @return self
     */
    public function setDateEffectue($dateEffectue) {
        $this->dateEffectue = $dateEffectue;
        return $this;
    }

    /**
     * Get dateFin
     *
     * @return date $dateFin
     */
    public function getDateFin() {
        return $this->dateFin;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return self
     */
    public function setLibelle($libelle) {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * Get libelle
     *
     * @return string $libelle
     */
    public function getLibelle() {
        return $this->libelle;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set technicien
     *
     * @param string $technicien
     * @return self
     */
    public function setTechnicien($technicien) {
        $this->technicien = $technicien;
        return $this;
    }

    /**
     * Get technicien
     *
     * @return string $technicien
     */
    public function getTechnicien() {
        return $this->technicien;
    }

    public function getDescriptionTransformed() {
        return str_replace('\n', "\n", $this->description);
    }

    public function getDuree() {
        if (!$this->dateFin || !$this->dateDebut) {
            return null;
        }
        $interval = $this->dateFin->diff($this->dateDebut);
        return $interval->format('%Hh%I');
    }

    public function isRealise() {
        return false;
        return boolval($this->dateEffectue);
    }

    public function isPlanifie() {
        return boolval($this->dateFin) && boolval($this->dateDebut);
    }
    public function isNonPlanifie() {
        return ! boolval($this->dateFin) || ! boolval($this->dateDebut);
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
}

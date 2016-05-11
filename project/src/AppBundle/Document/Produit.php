<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Behat\Transliterator\Transliterator;

/**
 * @MongoDB\EmbeddedDocument
 */
class Produit {

    const PRODUIT_ACTIF = "ACTIF";
    const PRODUIT_INACTIF = "INACTIF";

    /**
     * @MongoDB\String
     */
    protected $identifiant;

    /**
     * @MongoDB\String
     */
    protected $nom;

    /**
     * @MongoDB\Float
     */
    protected $prixHt;

    /**
     * @MongoDB\Float
     */
    protected $prixPrestation;

    /**
     * @MongoDB\Float
     */
    protected $prixVente;

    /**
     * @MongoDB\String
     */
    protected $conditionnement;

    /**
     * @MongoDB\Int
     */
    protected $nbTotalContrat;

    /**
     * @MongoDB\Int
     */
    protected $nbUtilisePassage;

    /**
     * @MongoDB\Int
     */
    protected $nbPremierPassage;

    /**
     * @MongoDB\String
     */
    protected $statut;

    /**
     * Set nom
     *
     * @param string $nom
     * @return self
     */
    public function setNom($nom) {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string $nom
     */
    public function getNom() {
        return $this->nom;
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

    public function __toString() {
        return $this->getNom();
    }

    /**
     * Set prixPrestation
     *
     * @param float $prixPrestation
     * @return self
     */
    public function setPrixPrestation($prixPrestation) {
        $this->prixPrestation = $prixPrestation;
        return $this;
    }

    /**
     * Get prixPrestation
     *
     * @return float $prixPrestation
     */
    public function getPrixPrestation() {
        return $this->prixPrestation;
    }

    /**
     * Set prixVente
     *
     * @param float $prixVente
     * @return self
     */
    public function setPrixVente($prixVente) {
        $this->prixVente = $prixVente;
        return $this;
    }

    /**
     * Get prixVente
     *
     * @return float $prixVente
     */
    public function getPrixVente() {
        return $this->prixVente;
    }

    /**
     * Set conditionnement
     *
     * @param string $conditionnement
     * @return self
     */
    public function setConditionnement($conditionnement) {
        $this->conditionnement = $conditionnement;
        return $this;
    }

    /**
     * Get conditionnement
     *
     * @return string $conditionnement
     */
    public function getConditionnement() {
        return $this->conditionnement;
    }

    /**
     * Set nbTotalContrat
     *
     * @param int $nbTotalContrat
     * @return self
     */
    public function setNbTotalContrat($nbTotalContrat) {
        $this->nbTotalContrat = $nbTotalContrat;
        return $this;
    }

    /**
     * Get nbTotalContrat
     *
     * @return int $nbTotalContrat
     */
    public function getNbTotalContrat() {
        return $this->nbTotalContrat;
    }

    /**
     * Set nbUtilisePassage
     *
     * @param int $nbUtilisePassage
     * @return self
     */
    public function setNbUtilisePassage($nbUtilisePassage) {
        $this->nbUtilisePassage = $nbUtilisePassage;
        return $this;
    }

    /**
     * Get nbUtilisePassage
     *
     * @return int $nbUtilisePassage
     */
    public function getNbUtilisePassage() {
        if($this->nbUtilisePassage === 0) {

            return null;
        }
        return $this->nbUtilisePassage;
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
     * Get statut
     *
     * @return string $statut
     */
    public function getStatut() {
        return $this->statut;
    }


    /**
     * Set nbPremierPassage
     *
     * @param int $nbPremierPassage
     * @return self
     */
    public function setNbPremierPassage($nbPremierPassage)
    {
        $this->nbPremierPassage = $nbPremierPassage;
        return $this;
    }

    /**
     * Get nbPremierPassage
     *
     * @return int $nbPremierPassage
     */
    public function getNbPremierPassage()
    {
        return $this->nbPremierPassage;
    }
}

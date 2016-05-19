<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Paiement {

    /**
     * @MongoDB\ReferenceOne(targetDocument="Facture", simple=true)
     */
    protected $facture;

    /**
     * @MongoDB\String
     */
    protected $moyenPaiement;

    /**
     * @MongoDB\String
     */
    protected $typeReglement;

    /**
     * @MongoDB\String
     */
    protected $libelle;

    /**
     * @MongoDB\Float
     */
    protected $montant;

    /**
     * @MongoDB\Date
     */
    protected $datePaiement;

    /**
     * @MongoDB\Boolean
     */
    protected $versementComptable;

    /**
     * Set facture
     *
     * @param AppBundle\Document\Facture $facture
     * @return self
     */
    public function setFacture(\AppBundle\Document\Facture $facture) {
        $this->facture = $facture;
        return $this;
    }

    /**
     * Get facture
     *
     * @return AppBundle\Document\Facture $facture
     */
    public function getFacture() {
        return $this->facture;
    }

    /**
     * Set moyenPaiement
     *
     * @param string $moyenPaiement
     * @return self
     */
    public function setMoyenPaiement($moyenPaiement) {
        $this->moyenPaiement = $moyenPaiement;
        return $this;
    }

    /**
     * Get moyenPaiement
     *
     * @return string $moyenPaiement
     */
    public function getMoyenPaiement() {
        return $this->moyenPaiement;
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
     * Set montant
     *
     * @param float $montant
     * @return self
     */
    public function setMontant($montant) {
        $this->montant = $montant;
        return $this;
    }

    /**
     * Get montant
     *
     * @return float $montant
     */
    public function getMontant() {
        return $this->montant;
    }

    /**
     * Set datePaiement
     *
     * @param date $datePaiement
     * @return self
     */
    public function setDatePaiement($datePaiement) {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    /**
     * Get datePaiement
     *
     * @return date $datePaiement
     */
    public function getDatePaiement() {
        return $this->datePaiement;
    }

    /**
     * Set versementComptable
     *
     * @param boolean $versementComptable
     * @return self
     */
    public function setVersementComptable($versementComptable) {
        $this->versementComptable = $versementComptable;
        return $this;
    }

    /**
     * Get versementComptable
     *
     * @return boolean $versementComptable
     */
    public function getVersementComptable() {
        return $this->versementComptable;
    }


    /**
     * Set typeReglement
     *
     * @param string $typeReglement
     * @return self
     */
    public function setTypeReglement($typeReglement)
    {
        $this->typeReglement = $typeReglement;
        return $this;
    }

    /**
     * Get typeReglement
     *
     * @return string $typeReglement
     */
    public function getTypeReglement()
    {
        return $this->typeReglement;
    }
}

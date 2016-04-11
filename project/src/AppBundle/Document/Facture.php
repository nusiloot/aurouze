<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\FactureRepository")
 */
class Facture {

    const PREFIX = "FACTURE";

    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Societe", inversedBy="factures")
     */
    protected $societe;

    /**
     * @MongoDB\String
     */
    protected $societeIdentifiant;

    /**
     * @MongoDB\Date
     */
    protected $dateEmission;

    /**
     * @MongoDB\Date
     */
    protected $dateFacturation;

    /**
     * @MongoDB\Date
     */
    protected $datePaiement;

    /**
     * @MongoDB\Float
     */
    protected $montantHT;

    /**
     * @MongoDB\Float
     */
    protected $montantTTC;

    /**
     * @MongoDB\Float
     */
    protected $montantTaxe;

    /**
     * @MongoDB\EmbedMany(targetDocument="FactureLigne")
     */
    protected $lignes;

    public function __construct()
    {
        $this->lignes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function generateId() {

        $this->setId(self::PREFIX . '-' . $this->getEtablissementIdentifiant() .'-' . $this->getDateEmission()->format('Ymd'));
    }

    public function update() {
        foreach($this->getLignes() as $ligne) {
            $ligne->update();
            $this->setMontantHT($this->getMontantHT() + $ligne->getMontantHT());
            $this->setMontantTaxe($this->getMontantTaxe() + $ligne->getMontantTaxe());
        }
        $this->setMontantHT(round($this->getMontantHT(), 2));
        $this->setMontantTTC(round($this->getMontantHT() + $this->getMontantTaxe()));
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
     * Add ligne
     *
     * @param AppBundle\Document\FactureLigne $ligne
     */
    public function addLigne(\AppBundle\Document\FactureLigne $ligne)
    {
        $this->lignes[] = $ligne;
    }

    /**
     * Remove ligne
     *
     * @param AppBundle\Document\FactureLigne $ligne
     */
    public function removeLigne(\AppBundle\Document\FactureLigne $ligne)
    {
        $this->lignes->removeElement($ligne);
    }

    /**
     * Get lignes
     *
     * @return \Doctrine\Common\Collections\Collection $lignes
     */
    public function getLignes()
    {
        return $this->lignes;
    }

    /**
     * Set montantHT
     *
     * @param float $montantHT
     * @return self
     */
    public function setMontantHT($montantHT)
    {
        $this->montantHT = $montantHT;
        return $this;
    }

    /**
     * Get montantHT
     *
     * @return float $montantHT
     */
    public function getMontantHT()
    {
        return $this->montantHT;
    }

    /**
     * Set montantTTC
     *
     * @param float $montantTTC
     * @return self
     */
    public function setMontantTTC($montantTTC)
    {
        $this->montantTTC = $montantTTC;
        return $this;
    }

    /**
     * Get montantTTC
     *
     * @return float $montantTTC
     */
    public function getMontantTTC()
    {
        return $this->montantTTC;
    }


    /**
     * Set montantTaxe
     *
     * @param float $montantTaxe
     * @return self
     */
    public function setMontantTaxe($montantTaxe)
    {
        $this->montantTaxe = $montantTaxe;
        return $this;
    }

    /**
     * Get montantTaxe
     *
     * @return float $montantTaxe
     */
    public function getMontantTaxe()
    {
        return $this->montantTaxe;
    }

    /**
     * Set dateEmission
     *
     * @param date $dateEmission
     * @return self
     */
    public function setDateEmission($dateEmission)
    {
        $this->dateEmission = $dateEmission;
        return $this;
    }

    /**
     * Get dateEmission
     *
     * @return date $dateEmission
     */
    public function getDateEmission()
    {
        return $this->dateEmission;
    }

    /**
     * Set dateFacturation
     *
     * @param date $dateFacturation
     * @return self
     */
    public function setDateFacturation($dateFacturation)
    {
        $this->dateFacturation = $dateFacturation;
        return $this;
    }

    /**
     * Get dateFacturation
     *
     * @return date $dateFacturation
     */
    public function getDateFacturation()
    {
        return $this->dateFacturation;
    }

    /**
     * Set datePaiement
     *
     * @param date $datePaiement
     * @return self
     */
    public function setDatePaiement($datePaiement)
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    /**
     * Get datePaiement
     *
     * @return date $datePaiement
     */
    public function getDatePaiement()
    {
        return $this->datePaiement;
    }
}

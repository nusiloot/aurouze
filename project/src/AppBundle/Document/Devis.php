<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Model\DocumentSocieteInterface;
use AppBundle\Manager\DevisManager;
use AppBundle\Manager\ContratManager;
use Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\DevisRepository") @HasLifecycleCallbacks
 */
class Devis implements DocumentSocieteInterface {

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\DevisGenerator"})
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Societe", inversedBy="devis", simple=true)
     */
    protected $societe;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Compte", inversedBy="devis")
     */
    protected $commercial;

    /**
     * @MongoDB\EmbedOne(targetDocument="FactureSoussigne")
     */
    protected $emetteur;

    /**
     * @MongoDB\EmbedOne(targetDocument="FactureSoussigne")
     */
    protected $destinataire;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $dateEmission;


    /**
     * @MongoDB\Field(type="date")
     */
    protected $dateSignature;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $montantHT;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $montantTTC;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $montantTaxe;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $description;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $numeroDevis;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $statut;

    /**
     * @MongoDB\EmbedMany(targetDocument="FactureLigne")
     */
    protected $lignes;


    public function __construct() {
        $this->emetteur = new FactureSoussigne();
        $this->destinataire = new FactureSoussigne();
        $this->statut = "NOUVEAU";
    }

    /**
     * Set societe
     *
     * @param AppBundle\Document\Societe $societe
     * @return self
     */
    public function setSociete(\AppBundle\Document\Societe $societe, $store = true) {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get societe
     *
     * @return AppBundle\Document\Societe $societe
     */
    public function getSociete() {
        return $this->societe;
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
     * Set commercial
     *
     * @param AppBundle\Document\Compte $commercial
     * @return $this
     */
    public function setCommercial(\AppBundle\Document\Compte $commercial)
    {
        $this->commercial = $commercial;
        return $this;
    }

    /**
     * Get commercial
     *
     * @return AppBundle\Document\Compte $commercial
     */
    public function getCommercial()
    {
        return $this->commercial;
    }

    /**
     * Set emetteur
     *
     * @param AppBundle\Document\FactureSoussigne $emetteur
     * @return self
     */
    public function setEmetteur(\AppBundle\Document\FactureSoussigne $emetteur) {
        $this->emetteur = $emetteur;
        return $this;
    }

    /**
     * Get emetteur
     *
     * @return AppBundle\Document\FactureSoussigne $emetteur
     */
    public function getEmetteur() {
        return $this->emetteur;
    }

    /**
     * Set destinataire
     *
     * @param AppBundle\Document\FactureSoussigne $destinataire
     * @return $this
     */
    public function setDestinataire(\AppBundle\Document\FactureSoussigne $destinataire)
    {
        $this->destinataire = $destinataire;
        return $this;
    }

    /**
     * Get destinataire
     *
     * @return AppBundle\Document\FactureSoussigne $destinataire
     */
    public function getDestinataire()
    {
        return $this->destinataire;
    }

    /**
     * Set dateEmission
     *
     * @param date $dateEmission
     * @return $this
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
     * Set dateSignature
     *
     * @param date $dateSignature
     * @return $this
     */
    public function setDateSignature($dateSignature)
    {
        $this->dateSignature = $dateSignature;
        return $this;
    }

    /**
     * Get dateSignature
     *
     * @return date $dateSignature
     */
    public function getDateSignature()
    {
        return $this->dateSignature;
    }

    /**
     * Set montantHT
     *
     * @param float $montantHT
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set numeroDevis
     *
     * @param string $numeroDevis
     * @return $this
     */
    public function setNumeroDevis($numeroDevis)
    {
        $this->numeroDevis = $numeroDevis;
        return $this;
    }

    /**
     * Get numeroDevis
     *
     * @return string $numeroDevis
     */
    public function getNumeroDevis()
    {
        return $this->numeroDevis;
    }

    /**
     * Set statut
     *
     * @param string $statut
     * @return $this
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
}

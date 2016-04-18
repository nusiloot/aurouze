<?php
namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
*/
class FactureLigne {

    /**
     * @MongoDB\String
     */
    protected $libelle;

    /**
     * @MongoDB\Float
     */
    protected $prixUnitaire;

    /**
     * @MongoDB\Float
     */
    protected $tauxTaxe;

    /**
     * @MongoDB\Float
     */
    protected $quantite;

    /**
     * @MongoDB\Float
     */
    protected $montantHT;

    /**
     * @MongoDB\Float
     */
    protected $montantTaxe;

    /**
     * @MongoDB\ReferenceOne(targetDocument="AppBundle\Document\Contrat")
     */
    protected $origineDocument;

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return self
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * Get libelle
     *
     * @return string $libelle
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set prixUnitaire
     *
     * @param float $prixUnitaire
     * @return self
     */
    public function setPrixUnitaire($prixUnitaire)
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    /**
     * Get prixUnitaire
     *
     * @return float $prixUnitaire
     */
    public function getPrixUnitaire()
    {
        return $this->prixUnitaire;
    }

    /**
     * Set quantite
     *
     * @param float $quantite
     * @return self
     */
    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;
        return $this;
    }

    /**
     * Get quantite
     *
     * @return float $quantite
     */
    public function getQuantite()
    {
        return $this->quantite;
    }

    public function update() {
        $this->montantHT = round($this->getQuantite() * $this->getPrixUnitaire(), 2);
        $this->montantTaxe = round($this->getMontantHT() * $this->getTauxTaxe(), 2);
    }

    public function isOrigineContrat() {

        return $this->getOrigineDocument() && $this->getOrigineDocument() instanceof \AppBundle\Document\Contrat;
    }

    /**
     * Set tauxTaxe
     *
     * @param float $tauxTaxe
     * @return self
     */
    public function setTauxTaxe($tauxTaxe)
    {
        $this->tauxTaxe = $tauxTaxe;
        return $this;
    }

    /**
     * Get tauxTaxe
     *
     * @return float $tauxTaxe
     */
    public function getTauxTaxe()
    {
        return $this->tauxTaxe;
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
     * Set origineDocument
     *
     * @param AppBundle\Model\DocumentFacturableInterface $origineDocument
     * @return self
     */
    public function setOrigineDocument(\AppBundle\Model\DocumentFacturableInterface $origineDocument)
    {
        $this->origineDocument = $origineDocument;
        return $this;
    }

    /**
     * Get origineDocument
     *
     * @return AppBundle\Model\DocumentFacturableInterface $origineDocument
     */
    public function getOrigineDocument()
    {
        return $this->origineDocument;
    }
}

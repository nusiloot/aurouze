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
    protected $tvaTaux;

    /**
     * @MongoDB\Float
     */
    protected $quantite;


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

    /**
     * Set tvaTaux
     *
     * @param float $tvaTaux
     * @return self
     */
    public function setTvaTaux($tvaTaux)
    {
        $this->tvaTaux = $tvaTaux;
        return $this;
    }

    /**
     * Get tvaTaux
     *
     * @return float $tvaTaux
     */
    public function getTvaTaux()
    {
        return $this->tvaTaux;
    }

    public function getMontantHT() {

        return $this->getPrixUnitaire() * $this->getQuantite();
    }
}

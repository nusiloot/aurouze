<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class ProduitAdmin {

    /**
     * @MongoDB\String
     */
    protected $nom;

    /**
     * @MongoDB\Float
     */
    protected $prixHt;

    public function __construct() {
        $this->prestationsTypes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->produits = new \Doctrine\Common\Collections\ArrayCollection();
    }

    

    /**
     * Set nom
     *
     * @param string $nom
     * @return self
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * Get nom
     *
     * @return string $nom
     */
    public function getNom()
    {
        return $this->nom;
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
}

<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 * @MongoDB\Index()
*/
class Mouvement {

    /**
     * @MongoDB\Float
     */
    protected $prix;

    /**
     * @MongoDB\Boolean
     */
    protected $facturable;

    /**
     * @MongoDB\Boolean
     */
    protected $facture;

    /**
     * Set prix
     *
     * @param float $prix
     * @return self
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;
        return $this;
    }

    /**
     * Get prix
     *
     * @return float $prix
     */
    public function getPrix()
    {
        return $this->prix;
    }

    /**
     * Set facturable
     *
     * @param boolean $facturable
     * @return self
     */
    public function setFacturable($facturable)
    {
        $this->facturable = $facturable;
        return $this;
    }

    /**
     * Get facturable
     *
     * @return boolean $facturable
     */
    public function getFacturable()
    {
        return $this->facturable;
    }

    /**
     * Set facture
     *
     * @param boolean $facture
     * @return self
     */
    public function setFacture($facture)
    {
        $this->facture = $facture;
        return $this;
    }

    /**
     * Get facture
     *
     * @return boolean $facture
     */
    public function getFacture()
    {
        return $this->facture;
    }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Adresse
 *
 * @author mathurin
 */

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Document\Coordonnees;

/** 
 * @MongoDB\EmbeddedDocument
 * @MongoDB\Index(keys={"coordonnees"="2d"})
*/
class Adresse {

    /**
     * @MongoDB\String
     */
    protected $adresse;

    /**
     * @MongoDB\String
     */
    protected $codePostal;

    /**
     * @MongoDB\String
     */
    protected $commune;

     /** 
     * @MongoDB\EmbedOne(targetDocument="Coordonnees") 
     */
    protected $coordonnees;

    /** 
     * @MongoDB\Distance 
     */
    protected $distance;

    public function __construct() {
        $this->coordonnees = new Coordonnees();
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return self
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
        return $this;
    }

    /**
     * Get adresse
     *
     * @return string $adresse
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set codePostal
     *
     * @param string $codePostal
     * @return self
     */
    public function setCodePostal($codePostal)
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string $codePostal
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set commune
     *
     * @param string $commune
     * @return self
     */
    public function setCommune($commune)
    {
        $this->commune = $commune;
        return $this;
    }

    /**
     * Get commune
     *
     * @return string $commune
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set coordonnees
     *
     * @param Coordonnees $coordonnees
     * @return self
     */
    public function setCoordonnees($coordonnees)
    {
        $this->coordonnees = $coordonnees;
        return $this;
    }

    /**
     * Get coordonnees
     *
     * @return Coordonnees $coordonnees
     */
    public function getCoordonnees()
    {
        return $this->coordonnees;
    }

    /**
     * Set distance
     *
     * @param string $distance
     * @return self
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * Get distance
     *
     * @return string $distance
     */
    public function getDistance()
    {
        return $this->distance;
    }

    public function getIntitule() {

        return sprintf("%s %s %s", $this->getAdresse(), $this->getCodePostal(), $this->getCommune());
    }
}

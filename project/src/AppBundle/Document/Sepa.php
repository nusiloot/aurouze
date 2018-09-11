<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sepa
 *
 */

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
*/
class Sepa {

    /**
     * @MongoDB\String
     */
    protected $iban;

    /**
     * @MongoDB\String
     */
    protected $bic;

    /**
     * @MongoDB\String
     */
    protected $rum;

    /**
     * @MongoDB\Date
     */
    protected $date;

    /**
     * @MongoDB\Boolean
     */
    protected $actif;

    public function __construct() {
        $this->setActif(false);
    }

    /**
     * Set iban
     *
     * @param string $iban
     * @return self
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * Get iban
     *
     * @return string $iban
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Set bic
     *
     * @param string $bic
     * @return self
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
        return $this;
    }

    /**
     * Get iban
     *
     * @return string $iban
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Set rum
     *
     * @param string $rum
     * @return self
     */
    public function setRum($rum)
    {
        $this->rum = $rum;
        return $this;
    }

    /**
     * Get iban
     *
     * @return string $iban
     */
    public function getRum()
    {
        return $this->rum;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date) {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return self
     */
    public function setActif($actif)
    {
        $this->actif = $actif;
        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean $actif
     */
    public function getActif()
    {
        return $this->actif;
    }

   
}

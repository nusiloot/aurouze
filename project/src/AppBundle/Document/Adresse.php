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

/** 
 * @MongoDB\EmbeddedDocument
 * @MongoDB\Index(keys={"coordinates"="2d"})
*/
class Adresse {

    /**
     * @MongoDB\String
     */
    protected $adresse;

    /**
     * @MongoDB\String
     */
    protected $code_postal;

    /**
     * @MongoDB\String
     */
    protected $commune;

    /**
     * @MongoDB\String
     */
    protected $telephone_portable;

    /**
     * @MongoDB\String
     */
    protected $telephone_fixe;

    /**
     * @MongoDB\String
     */
    protected $fax;

    
     /** 
     * @MongoDB\EmbedOne(targetDocument="Coordinates") 
     */
    protected $coordinates;

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
        $this->code_postal = $codePostal;
        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string $codePostal
     */
    public function getCodePostal()
    {
        return $this->code_postal;
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
     * Set telephonePortable
     *
     * @param string $telephonePortable
     * @return self
     */
    public function setTelephonePortable($telephonePortable)
    {
        $this->telephone_portable = $telephonePortable;
        return $this;
    }

    /**
     * Get telephonePortable
     *
     * @return string $telephonePortable
     */
    public function getTelephonePortable()
    {
        return $this->telephone_portable;
    }

    /**
     * Set telephoneFixe
     *
     * @param string $telephoneFixe
     * @return self
     */
    public function setTelephoneFixe($telephoneFixe)
    {
        $this->telephone_fixe = $telephoneFixe;
        return $this;
    }

    /**
     * Get telephoneFixe
     *
     * @return string $telephoneFixe
     */
    public function getTelephoneFixe()
    {
        return $this->telephone_fixe;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return self
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * Get fax
     *
     * @return string $fax
     */
    public function getFax()
    {
        return $this->fax;
    }
    
     /**
     * Get adressecomplete
     *
     * @return string $adressecomplete
     */
    public function getAdressecomplete() {
        return $this->adresse." ".$this->code_postal." ".$this->commune;
    }

    /**
     * Set coordinates
     *
     * @param AppBundle\Document\Coordinates $coordinates
     * @return self
     */
    public function setCoordinates(\AppBundle\Document\Coordinates $coordinates)
    {
        $this->coordinates = $coordinates;
        return $this;
    }

    /**
     * Get coordinates
     *
     * @return AppBundle\Document\Coordinates $coordinates
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Passages
 *
 * @author mathurin
 */
namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
/**
 * @MongoDB\Document
 */
class Passages {
    /**
     * @MongoDB\Id
     */
    protected $id;
    
    /**
     * @MongoDB\String
     */
    protected $etablissementId;
    
    /**
     * @MongoDB\String
     */
    protected $societeId;
    
    /**
     * @MongoDB\String
     */
    protected $telephone;

    /**
     * @MongoDB\String
     */
    protected $name;

    /**
     * @MongoDB\Float
     */
    protected $price;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __construct($etablissement,$contrat) {
        
        $this->setId('PASSAGE-'.$etablissement->getId().'-'.$contrat->getId().'-'.$numPassage);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return float $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set etablissementId
     *
     * @param string $etablissementId
     * @return self
     */
    public function setEtablissementId($etablissementId)
    {
        $this->etablissementId = $etablissementId;
        return $this;
    }

    /**
     * Get etablissementId
     *
     * @return string $etablissementId
     */
    public function getEtablissementId()
    {
        return $this->etablissementId;
    }

    /**
     * Set societeId
     *
     * @param string $societeId
     * @return self
     */
    public function setSocieteId($societeId)
    {
        $this->societeId = $societeId;
        return $this;
    }

    /**
     * Get societeId
     *
     * @return string $societeId
     */
    public function getSocieteId()
    {
        return $this->societeId;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return self
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * Get telephone
     *
     * @return string $telephone
     */
    public function getTelephone()
    {
        return $this->telephone;
    }
}

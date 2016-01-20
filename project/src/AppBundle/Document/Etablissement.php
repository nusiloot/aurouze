<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Document;

/**
 * Description of Etablissement
 *
 * @author mathurin
 */

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\EtablissementRepository")
 */
class Etablissement {

    const PREFIX = "ETABLISSEMENT";
    
    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;
    
    /**
     * @MongoDB\string
     */
    protected $identifiant;

    
    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set id
     *
     * @return id $id
     */
    public function setId()
    {
        $this->id = generateId();
    }
    
    public function generateId() {
        return self::PREFIX.'-'.$this->identifiant;
    }


    /**
     * Set identifiant
     *
     * @param string $identifiant
     * @return self
     */
    public function setIdentifiant($identifiant)
    {
        $this->identifiant = $identifiant;
        return $this;
    }

    /**
     * Get identifiant
     *
     * @return string $identifiant
     */
    public function getIdentifiant()
    {
        return $this->identifiant;
    }
}

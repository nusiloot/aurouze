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
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\ConfigurationRepository")
 */
class Configuration {

    const PREFIX = "CONFIGURATION";

    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\EmbedMany(targetDocument="PrestationAdmin")
     */
    protected $prestationsAdmin;
    
    /**
     * @MongoDB\EmbedMany(targetDocument="ProduitAdmin")
     */
    protected $produitsAdmin;

    public function __construct()
    {
        $this->prestationsAdmin = new \Doctrine\Common\Collections\ArrayCollection();
        $this->produitsAdmin = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set id
     *
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * Add prestationsAdmin
     *
     * @param AppBundle\Document\PrestationAdmin $prestationsAdmin
     */
    public function addPrestationsAdmin(\AppBundle\Document\PrestationAdmin $prestationsAdmin)
    {
        $this->prestationsAdmin[] = $prestationsAdmin;
    }

    /**
     * Remove prestationsAdmin
     *
     * @param AppBundle\Document\PrestationAdmin $prestationsAdmin
     */
    public function removePrestationsAdmin(\AppBundle\Document\PrestationAdmin $prestationsAdmin)
    {
        $this->prestationsAdmin->removeElement($prestationsAdmin);
    }

    /**
     * Get prestationsAdmin
     *
     * @return \Doctrine\Common\Collections\Collection $prestationsAdmin
     */
    public function getPrestationsAdmin()
    {
        return $this->prestationsAdmin;
    }

    /**
     * Add produitsAdmin
     *
     * @param AppBundle\Document\ProduitAdmin $produitsAdmin
     */
    public function addProduitsAdmin(\AppBundle\Document\ProduitAdmin $produitsAdmin)
    {
        $this->produitsAdmin[] = $produitsAdmin;
    }

    /**
     * Remove produitsAdmin
     *
     * @param AppBundle\Document\ProduitAdmin $produitsAdmin
     */
    public function removeProduitsAdmin(\AppBundle\Document\ProduitAdmin $produitsAdmin)
    {
        $this->produitsAdmin->removeElement($produitsAdmin);
    }

    /**
     * Get produitsAdmin
     *
     * @return \Doctrine\Common\Collections\Collection $produitsAdmin
     */
    public function getProduitsAdmin()
    {
        return $this->produitsAdmin;
    }
}

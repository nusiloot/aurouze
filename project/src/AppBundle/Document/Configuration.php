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
     * @MongoDB\EmbedMany(targetDocument="ConfigurationPrestation")
     */
    protected $prestations;
    
    /**
     * @MongoDB\EmbedMany(targetDocument="ConfigurationProduit")
     */
    protected $produits;

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
     * Add prestation
     *
     * @param AppBundle\Document\ConfigurationPrestation $prestation
     */
    public function addPrestation(\AppBundle\Document\ConfigurationPrestation $prestation)
    {
        $this->prestations[] = $prestation;
    }

    /**
     * Remove prestation
     *
     * @param AppBundle\Document\ConfigurationPrestation $prestation
     */
    public function removePrestation(\AppBundle\Document\ConfigurationPrestation $prestation)
    {
        $this->prestations->removeElement($prestation);
    }

    /**
     * Get prestations
     *
     * @return \Doctrine\Common\Collections\Collection $prestations
     */
    public function getPrestations()
    {
        return $this->prestations;
    }

    /**
     * Add produit
     *
     * @param AppBundle\Document\ConfigurationProduit $produit
     */
    public function addProduit(\AppBundle\Document\ConfigurationProduit $produit)
    {
        $this->produits[] = $produit;
    }

    /**
     * Remove produit
     *
     * @param AppBundle\Document\ConfigurationProduit $produit
     */
    public function removeProduit(\AppBundle\Document\ConfigurationProduit $produit)
    {
        $this->produits->removeElement($produit);
    }

    /**
     * Get produits
     *
     * @return \Doctrine\Common\Collections\Collection $produits
     */
    public function getProduits()
    {
        return $this->produits;
    }
}

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
use AppBundle\Document\Prestation;
use AppBundle\Document\Produit;
use AppBundle\Document\Provenance;

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
     * @MongoDB\EmbedMany(targetDocument="Prestation")
     */
    protected $prestations;

    /**
     * @MongoDB\EmbedMany(targetDocument="Produit")
     */
    protected $produits;

    /**
     * @MongoDB\EmbedMany(targetDocument="Provenance")
     */
    protected $provenances;

    public function __construct() {
        $this->prestations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->produits = new \Doctrine\Common\Collections\ArrayCollection();
        $this->provenances = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param string $id
     * @return self
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Add prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function addPrestation(\AppBundle\Document\Prestation $prestation) {
        $this->prestations[] = $prestation;
    }

    /**
     * Remove prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function removePrestation(\AppBundle\Document\Prestation $prestation) {
        $this->prestations->removeElement($prestation);
    }

    /**
     * Get prestations
     *
     * @return \Doctrine\Common\Collections\Collection $prestations
     */
    public function getPrestations() {
        return $this->prestations;
    }

    /**
     * Add produit
     *
     * @param AppBundle\Document\Produit $produit
     */
    public function addProduit(\AppBundle\Document\Produit $produit) {
        $this->produits[] = $produit;
    }

    /**
     * Remove produit
     *
     * @param AppBundle\Document\ConfigurationProduit $produit
     */
    public function removeProduit(\AppBundle\Document\Produit $produit) {
        $this->produits->removeElement($produit);
    }

    /**
     * Get produits
     *
     * @return \Doctrine\Common\Collections\Collection $produits
     */
    public function getProduits() {
        return $this->produits;
    }

    public function getPrestationsArray() {
        $prestationsType = array();
        foreach ($this->getPrestations() as $prestation) {
            $prestationsType[$prestation->getIdentifiant()] = $prestation;
        }
        return $prestationsType;
    }

    public function getProduitsArray() {
        $produitsType = array();
        foreach ($this->getProduits() as $produit) {
            $produitsType[$produit->getIdentifiant()] = $produit;
        }
        return $produitsType;
    }

    public function getProduitsArrayOrdered() {
        $produitsArray = $this->getProduitsArray();
        uasort($produitsArray,array("AppBundle\Document\Configuration", "cmpProduitByOrdre"));
        return $produitsArray;
    }

    public static function cmpProduitByOrdre($a, $b) {
        if ($a->getOrdre() == $b->getOrdre()) {
                return "0";
            } else {
                return ($b->getOrdre() > $a->getOrdre()) ? 1 : -1;
            }
    }


    /**
     * Add provenance
     *
     * @param AppBundle\Document\Provenance $provenance
     */
    public function addProvenance(\AppBundle\Document\Provenance $provenance)
    {
        $this->provenances[] = $provenance;
    }

    /**
     * Remove provenance
     *
     * @param AppBundle\Document\Provenance $provenance
     */
    public function removeProvenance(\AppBundle\Document\Provenance $provenance)
    {
        $this->provenances->removeElement($provenance);
    }

    /**
     * Get provenances
     *
     * @return \Doctrine\Common\Collections\Collection $provenances
     */
    public function getProvenances()
    {
        return $this->provenances;
    }

    public function getProduitByIdentifiant($identifiant){
      foreach ($this->getProduits() as $produit) {
        if($identifiant == $produit->getIdentifiant()){
          return $produit;
        }
      }
      return null;
    }

}

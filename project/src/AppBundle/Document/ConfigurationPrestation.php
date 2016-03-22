<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Behat\Transliterator\Transliterator;

/**
 * @MongoDB\EmbeddedDocument
 */
class ConfigurationPrestation
{

    /**
     * @MongoDB\String
     */
    protected $id;
    
    /**
     * @MongoDB\String
     */
    protected $nom;

    


    /**
     * Set prestationKey
     *
     * @param string $prestationKey
     * @return self
     */
    public function setPrestationKey($prestationKey)
    {
        $this->prestationKey = $prestationKey;
        return $this;
    }

    /**
     * Get prestationKey
     *
     * @return string $prestationKey
     */
    public function getPrestationKey()
    {
        return $this->prestationKey;
    }

    /**
     * Set prestationNom
     *
     * @param string $prestationNom
     * @return self
     */
    public function setPrestationNom($prestationNom)
    {
        $this->prestationNom = $prestationNom;
        $this->setPrestationKey(Transliterator::urlize($prestationNom));
        return $this;
    }

    /**
     * Get prestationNom
     *
     * @return string $prestationNom
     */
    public function getPrestationNom()
    {
        return $this->prestationNom;
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
}

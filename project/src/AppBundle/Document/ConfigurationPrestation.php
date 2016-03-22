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
    protected $prestationKey;
    
    /**
     * @MongoDB\String
     */
    protected $prestationNom;

    


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
}

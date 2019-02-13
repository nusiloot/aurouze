<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Behat\Transliterator\Transliterator;

/**
 * @MongoDB\EmbeddedDocument
 */
class NiveauInfestation {

    /**
     * @MongoDB\Field(type="string")
     */
    protected $identifiant;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $infestation;


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

    /**
     * Set infestation
     *
     * @param string $infestation
     * @return self
     */
    public function setInfestation($infestation)
    {
        $this->infestation = $infestation;
        return $this;
    }

    /**
     * Get infestation
     *
     * @return string $infestation
     */
    public function getInfestation()
    {
        return $this->infestation;
    }
}

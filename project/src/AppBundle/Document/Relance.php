<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 * @MongoDB\Index()
*/
class Relance {

    /**
     * @MongoDB\Field(type="date")
     */
    protected $dateRelance;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $numeroRelance;


    /**
     * Set dateRelance
     *
     * @param date $dateRelance
     * @return self
     */
    public function setDateRelance($dateRelance)
    {
        $this->dateRelance = $dateRelance;
        return $this;
    }

    /**
     * Get dateRelance
     *
     * @return date $dateRelance
     */
    public function getDateRelance()
    {
        return $this->dateRelance;
    }

    /**
     * Set numeroRelance
     *
     * @param int $numeroRelance
     * @return self
     */
    public function setNumeroRelance($numeroRelance)
    {
        $this->numeroRelance = $numeroRelance;
        return $this;
    }

    /**
     * Get numeroRelance
     *
     * @return int $numeroRelance
     */
    public function getNumeroRelance()
    {
        return $this->numeroRelance;
    }
}

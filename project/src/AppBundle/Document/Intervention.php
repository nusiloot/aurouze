<?php
namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** 
 * @MongoDB\EmbeddedDocument
*/
class Intervention {

    /**
     * @MongoDB\EmbedMany(targetDocument="Prestation")
     */
    protected $prestations;

    /**
     * @MongoDB\Boolean
     */
    protected $facturable;

    
    public function __construct()
    {
        $this->prestations = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function addPrestation(\AppBundle\Document\Prestation $prestation)
    {
        $this->prestations[] = $prestation;
    }

    /**
     * Remove prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function removePrestation(\AppBundle\Document\Prestation $prestation)
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
     * Set facturable
     *
     * @param boolean $facturable
     * @return self
     */
    public function setFacturable($facturable)
    {
        $this->facturable = $facturable;
        return $this;
    }

    /**
     * Get facturable
     *
     * @return boolean $facturable
     */
    public function getFacturable()
    {
        return $this->facturable;
    }
}

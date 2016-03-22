<?php
namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** 
 * @MongoDB\EmbeddedDocument
*/
class Intervention {

    /**
     * @MongoDB\Hash
     */
    protected $prestations;

    /**
     * @MongoDB\Boolean
     */
    protected $facturable;

    
    public function __construct()
    {
        $this->prestations = array();
    }

    /**
     * Get prestations
     *
     * @return collection $prestations
     */
    public function getPrestations() {
        return $this->prestations;
    }

    /**
     * Set prestations
     *
     * @param collection $prestations
     * @return self
     */
    public function setPrestations($prestations)
    {
        $this->prestations = $prestations;
        return $this;
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

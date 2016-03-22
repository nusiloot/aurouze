<?php
namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** 
 * @MongoDB\EmbeddedDocument
*/
class Prestation {


    /**
     * @MongoDB\String
     */
    protected $id;
    
    /**
     * @MongoDB\String
     */
    protected $nom;


    /**
     * @MongoDB\String
     */
    protected $nbPassages;



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

    /**
     * Set nbPassages
     *
     * @param string $nbPassages
     * @return self
     */
    public function setNbPassages($nbPassages)
    {
        $this->nbPassages = $nbPassages;
        return $this;
    }

    /**
     * Get nbPassages
     *
     * @return string $nbPassages
     */
    public function getNbPassages()
    {
        return $this->nbPassages;
    }
}

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
    protected $prestationType;

    /**
     * @MongoDB\String
     */
    protected $animalType;

    /**
     * @MongoDB\String
     */
    protected $animal;

     /** 
     * @MongoDB\String
     */
    protected $localisation;

     /** 
     * @MongoDB\String
     */
    protected $commentaire;

    

    /**
     * Set prestationType
     *
     * @param string $prestationType
     * @return self
     */
    public function setPrestationType($prestationType)
    {
        $this->prestationType = $prestationType;
        return $this;
    }

    /**
     * Get prestationType
     *
     * @return string $prestationType
     */
    public function getPrestationType()
    {
        return $this->prestationType;
    }

    /**
     * Set animalType
     *
     * @param string $animalType
     * @return self
     */
    public function setAnimalType($animalType)
    {
        $this->animalType = $animalType;
        return $this;
    }

    /**
     * Get animalType
     *
     * @return string $animalType
     */
    public function getAnimalType()
    {
        return $this->animalType;
    }

    /**
     * Set animal
     *
     * @param string $animal
     * @return self
     */
    public function setAnimal($animal)
    {
        $this->animal = $animal;
        return $this;
    }

    /**
     * Get animal
     *
     * @return string $animal
     */
    public function getAnimal()
    {
        return $this->animal;
    }

    /**
     * Set localisation
     *
     * @param string $localisation
     * @return self
     */
    public function setLocalisation($localisation)
    {
        $this->localisation = $localisation;
        return $this;
    }

    /**
     * Get localisation
     *
     * @return string $localisation
     */
    public function getLocalisation()
    {
        return $this->localisation;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return self
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string $commentaire
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }
}

<?php

namespace AppBundle\Document;

/**
 * AppBundle\Document\EtablissementInfos
 */
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Model\EtablissementInfosInterface;
use AppBundle\Document\Adresse;

/** 
 * @MongoDB\EmbeddedDocument
 * @MongoDB\Index(keys={"coordonnees"="2d"})
*/
class EtablissementInfos implements EtablissementInfosInterface {

    /**
     * @MongoDB\String
     */
    protected $nom;

    /**
     * @MongoDB\String
     */
    protected $contact;

    /**
     * @MongoDB\EmbedOne(targetDocument="Adresse")
     */
    protected $adresse;

    /**
     * @MongoDB\String
     */
    protected $type;

    public function __construct() {
        $this->adresse = new Adresse();
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
     * Set contact
     *
     * @param string $contact
     * @return self
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * Get contact
     *
     * @return string $contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set adresse
     *
     * @param Adresse $adresse
     * @return self
     */
    public function setAdresse(Adresse $adresse)
    {
        $this->adresse = $adresse;
        return $this;
    }

    /**
     * Get adresse
     *
     * @return Adresse $adresse
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    public function getTelephoneFixe() {

    }

    public function getTelephonePortable() {
        
    }

    public function getFax() {
        
    }

    public function getIcon() {

        return EtablissementManager::$type_icon[$this->getType()];
    }
    
    public function getIntitule() {

        return $this->getNom() . ' ' . $this->getAdresse()->getIntitule();
    }

    public function pull(EtablissementInfosInterface $etablissementFrom) {
        $this->setNom($etablissementFrom->getNom());
        $this->setContact($etablissementFrom->getContact());
        $this->setAdresse(clone $etablissementFrom->getAdresse());
        $this->setType($etablissementFrom->getType());
    }
}

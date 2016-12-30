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
     * @MongoDB\EmbedOne(targetDocument="Adresse")
     */
    protected $adresse;

    /**
     * @MongoDB\EmbedOne(targetDocument="ContactCoordonnee")
     */
    protected $contactCoordonnee;

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

    public function getLibelle() {
        if (!$this->getContactCoordonnee()) {
            return null;
        }
        return $this->getContactCoordonnee()->getLibelle();
    }

    public function getTelephoneFixe() {
        if (!$this->getContactCoordonnee()) {
            return null;
        }
        return $this->getContactCoordonnee()->getTelephoneFixe();
    }

    public function getTelephonePortable() {
        if (!$this->getContactCoordonnee()) {
            return null;
        }
        return $this->getContactCoordonnee()->getTelephoneMobile();
    }

    public function getFax() {
        if (!$this->getContactCoordonnee()) {
            return null;
        }
        return $this->getContactCoordonnee()->getFax();
    }

    public function getEmail() {
        if (!$this->getContactCoordonnee()) {
            return null;
        }
        return $this->getContactCoordonnee()->getEmail();
    }

    public function getIcon() {

        return EtablissementManager::$type_icon[$this->getType()];
    }

    public function getIntitule() {

        return $this->getNom() . ' ' . $this->getAdresse()->getIntitule();
    }

    public function pull(EtablissementInfosInterface $etablissementFrom) {
        $this->setNom($etablissementFrom->getNom(false));
        $this->setAdresse(clone $etablissementFrom->getAdresse());
        $this->setContactCoordonnee(clone $etablissementFrom->getContactCoordonnee());
        $this->setType($etablissementFrom->getType());
    }

    /**
     * Set contactCoordonnee
     *
     * @param AppBundle\Document\ContactCoordonnee $contactCoordonnee
     * @return self
     */
    public function setContactCoordonnee(\AppBundle\Document\ContactCoordonnee $contactCoordonnee)
    {
        $this->contactCoordonnee = $contactCoordonnee;
        return $this;
    }

    /**
     * Get contactCoordonnee
     *
     * @return AppBundle\Document\ContactCoordonnee $contactCoordonnee
     */
    public function getContactCoordonnee()
    {
        return $this->contactCoordonnee;
    }
}

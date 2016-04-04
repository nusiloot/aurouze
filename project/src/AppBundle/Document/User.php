<?php
namespace AppBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Manager\UserManager;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User {

    const PREFIX = "USER";
    const USER_TYPE_TECHNICIEN = "TECHNICIEN";
    const USER_TYPE_COMMERCIAL = "COMMERCIAL";
    const COULEUR_DEFAUT = 'yellow';

    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\string
     */
    protected $identifiant;

    /**
     * @MongoDB\String
     */
    protected $nom;

    /**
     * @MongoDB\String
     */
    protected $prenom;

    /**
     * @MongoDB\String
     */
    protected $identite;

    /**
     * @MongoDB\String
     */
    protected $couleur;

    /**
     * @MongoDB\String
     */
    protected $type;
    
    /***
     *  @MongoDB\ReferenceMany(targetDocument="Passage", mappedBy="etablissement") 
     */
    protected $passages = array();

    public function generateId() {
        $this->setId(self::PREFIX . '-' . $this->identifiant);
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set id
     *
     * @return id $id
     */

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

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
     * Set prenom
     *
     * @param string $prenom
     * @return self
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
        return $this;
    }

    /**
     * Get prenom
     *
     * @return string $prenom
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set identite
     *
     * @param string $identite
     * @return self
     */
    public function setIdentite($identite)
    {
        $this->identite = $identite;
        return $this;
    }

    /**
     * Get identite
     *
     * @return string $identite
     */
    public function getIdentite()
    {
        return $this->identite;
    }

    /**
     * Set couleur
     *
     * @param string $couleur
     * @return self
     */
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;
        return $this;
    }

    /**
     * Get couleur
     *
     * @return string $couleur
     */
    public function getCouleur() {
        if(!$this->couleur) {

            return '#ffffff';
        }
        return $this->couleur;
    }

    public function getCouleurText() {
        if(!$this->getCouleur() || $this->getCouleur() == '#ffffff') {

            return '#000000';
        }

        return '#ffffff';
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

    public function getInituleCourt() {

        return $this->getPrenom();
    }
    
    public function __toString() {
    	return $this->getIdentite();
    }
}

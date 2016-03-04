<?php
namespace AppBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User {

    const PREFIX = "USER";

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
    protected $type_user;


    /**
     * Set id
     *
     * @param string $id
     * @return self
     */
     public function setId() {
        $this->id = $this->generateId();
        return $this;
    }

    public function generateId() {
        return self::PREFIX . '-' . $this->identifiant;
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
    public function getCouleur()
    {
        return $this->couleur;
    }
    

    /**
     * Set typeUser
     *
     * @param string $typeUser
     * @return self
     */
    public function setTypeUser($typeUser)
    {
        $this->type_user = $typeUser;
        return $this;
    }

    /**
     * Get typeUser
     *
     * @return string $typeUser
     */
    public function getTypeUser()
    {
        return $this->type_user;
    }
}

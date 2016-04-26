<?php
namespace AppBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Manager\CompteManager;
use AppBundle\Model\DocumentSocieteInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\CompteRepository")
 */
class Compte implements DocumentSocieteInterface {

    const PREFIX = "COMPTE";
    const COULEUR_DEFAUT = 'yellow';

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\CompteGenerator"})
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
     * @MongoDB\ReferenceOne(targetDocument="Societe", inversedBy="comptes")
     */
    protected $societe;
    
    /**
     *  @MongoDB\ReferenceMany(targetDocument="Passage", mappedBy="techniciens") 
     */
    protected $passages = array();

     /**
     * @MongoDB\Boolean
     */
    protected $actif;

    /**
     *  @MongoDB\EmbedMany(targetDocument="CompteTag")
     */
    protected $tags = array();
    
    

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
        return $this->prenom.' '.$this->nom;
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

    

    public function getInituleCourt() {

        return $this->getPrenom();
    }
    
    public function __toString() {
    	return $this->getIdentite();
    }
    
    public function __construct(Societe $societe)
    {
        $this->passages = new ArrayCollection();
        $this->prestations = new ArrayCollection();
        $this->setSociete($societe);
    }
    
    /**
     * Add passage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function addPassage(\AppBundle\Document\Passage $passage)
    {
        $this->passages[] = $passage;
    }

    /**
     * Remove passage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function removePassage(\AppBundle\Document\Passage $passage)
    {
        $this->passages->removeElement($passage);
    }

    /**
     * Get passages
     *
     * @return \Doctrine\Common\Collections\Collection $passages
     */
    public function getPassages()
    {
        return $this->passages;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return self
     */
    public function setActif($actif)
    {
        $this->actif = $actif;
        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean $actif
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Get societe
     *
     * @return AppBundle\Document\Societe $societe
     */
    public function getSociete()
    {
        return $this->societe;
    }


    /**
     * Set societe
     *
     * @param AppBundle\Document\Societe $societe
     * @return self
     */
    public function setSociete(\AppBundle\Document\Societe $societe)
    {
        $this->societe = $societe;
        return $this;
    }


    /**
     * Add tag
     *
     * @param AppBundle\Document\CompteTag $tag
     */
    public function addTag(\AppBundle\Document\CompteTag $tag)
    {
        foreach ($this->getTags() as $t) {
            if ($t->getIdentifiant() == $tag->getIdentifiant()) {
                return;
            }
        }
        
        $this->tags[] = $tag;
    }

    /**
     * Remove tag
     *
     * @param AppBundle\Document\CompteTag $tag
     */
    public function removeTag(\AppBundle\Document\CompteTag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection $tags
     */
    public function getTags()
    {
        return $this->tags;
    }
}

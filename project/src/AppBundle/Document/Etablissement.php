<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks;
use AppBundle\Model\EtablissementInfosInterface;
use AppBundle\Model\DocumentSocieteInterface;
use AppBundle\Document\Adresse;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Document\Contrat;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\EtablissementRepository") @HasLifecycleCallbacks
 */
class Etablissement implements DocumentSocieteInterface, EtablissementInfosInterface {

    const PREFIX = "ETABLISSEMENT";

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\EtablissementGenerator"})
     */
    protected $id;

    /**
     * @MongoDB\string
     */
    protected $identifiant;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Societe", inversedBy="etablissements", simple=true)
     */
    protected $societe;

    /**
     * @MongoDB\String
     */
    protected $raisonSociale;

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

    /**
     * @MongoDB\String
     */
    protected $identifiantReprise;

    /**
     *  @MongoDB\ReferenceMany(targetDocument="Contrat", mappedBy="etablissement")
     */
    protected $contrats = array();

    /**
     * @MongoDB\Increment
     */
    protected $numeroPassageIncrement;

    /**
     * @MongoDB\String
     */
    protected $commentaire;
    
    /**
     * @MongoDB\Boolean
     */
    protected $actif;

    public function __construct() {
        $this->adresse = new Adresse();
        $this->contactCoordonnee = new ContactCoordonnee();
    }

    /** @MongoDB\PreUpdate */
    public function preUpdate() {
        $this->pullInfosFromSociete();
    }

    /** @MongoDB\PrePersist */
    public function prePersist() {
        $this->pullInfosFromSociete();
    }

    public function getSameAdresse() {
    	return $this->isSameAdresseThanSociete();
    }

    public function setSameAdresse($value) {
    	return $this;
    }

    public function getSameContact() {
    	return $this->isSameContactCoordonneeThanSociete();
    }

    public function setSameContact($value) {
    	return $this;
    }

    public function isSameAdresseThanSociete() {

        return $this->getAdresse()->isSameThan($this->getSociete()->getAdresse());
    }

    public function isSameContactCoordonneeThanSociete() {

        return $this->getContactCoordonnee()->isSameThan($this->getSociete()->getContactCoordonnee());
    }

    public function pullInfosFromSociete() {
        if($this->isSameAdresseThanSociete()) {
            $this->getAdresse()->copyFrom($this->getSociete()->getAdresse());
        }
        if($this->isSameContactCoordonneeThanSociete()) {
            $this->getContactCoordonnee()->copyFrom($this->getSociete()->getContactCoordonnee());
        }
    }

    /**
     * Set identifiant
     *
     * @param string $identifiant
     * @return self
     */
    public function setIdentifiant($identifiant) {
        $this->identifiant = $identifiant;
        return $this;
    }

    /**
     * Get identifiant
     *
     * @return string $identifiant
     */
    public function getIdentifiant() {
        return $this->identifiant;
    }

    /**
     * Set contrats
     *
     * @param array $contrats
     * @return self
     */
    public function setContrats($contrats) {
        $this->contrats = $contrats;
        return $this;
    }

    /**
     * Add contrats
     *
     * @param Contrat $contrat
     * @return self
     */
    public function addContrat($contrat) {
        $this->contrats[] = $contrat;
        return $this;
    }

    /**
     * Get contrats
     *
     * @return array contrats
     */
    public function getContrats() {
        return $this->contrats;
    }

    /**
     * Set raisonSociale
     *
     * @param string $raisonSociale
     * @return self
     */
    public function setRaisonSociale($raisonSociale) {
        $this->raisonSociale = $raisonSociale;
        return $this;
    }

    /**
     * Get raisonSociale
     *
     * @return string $raisonSociale
     */
    public function getRaisonSociale() {
        return $this->raisonSociale;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return self
     */
    public function setCommentaire($commentaire) {
        $this->commentaire = $commentaire;
        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string $commentaire
     */
    public function getCommentaire() {
        return $this->commentaire;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return self
     */
    public function setNom($nom) {
        $this->nom = $nom;
        return $this;
    }

    /**
     * Get nom
     *
     * @return string $nom
     */
    public function getNom() {
        return $this->nom;
    }

    /**
     * Set adresse
     *
     * @param Adresse $adresse
     * @return self
     */
    public function setAdresse(Adresse $adresse) {
        $this->adresse = $adresse;
        return $this;
    }

    /**
     * Get adresse
     *
     * @return Adresse $adresse
     */
    public function getAdresse() {
        return $this->adresse;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType() {
        return $this->type;
    }

    public function getTelephoneFixe() {
        if(!$this->getContactCoordonnee()){
            return null;
        }
        return $this->getContactCoordonnee()->getTelephoneFixe();
    }

    public function getTelephonePortable() {
        if(!$this->getContactCoordonnee()){
            return null;
        }
        return $this->getContactCoordonnee()->getTelephoneMobile();
    }

    public function getFax() {
        if(!$this->getContactCoordonnee()){
            return null;
        }
        return $this->getContactCoordonnee()->getFax();
    }

    public function getEmail() {
        if(!$this->getContactCoordonnee()){
            return null;
        }
        return $this->getContactCoordonnee()->getEmail();
    }

     public function getSiteInternet() {
        if(!$this->getContactCoordonnee()){
            return null;
        }
        return $this->getContactCoordonnee()->getSiteInternet();
    }


    public function getIcon() {

        return EtablissementManager::$type_icon[$this->getType()];
    }

    public function getIntitule() {

        return $this->getNom() . ' ' . $this->getAdresse()->getIntitule() . ' (' . $this->identifiant . ')';
    }

    /**
     * Set societe
     *
     * @param AppBundle\Document\Societe $societe
     * @return self
     */
    public function setSociete(\AppBundle\Document\Societe $societe) {
        $this->societe = $societe;
        return $this;
    }

    /**
     * Get societe
     *
     * @return AppBundle\Document\Societe $societe
     */
    public function getSociete() {
        return $this->societe;
    }

    /**
     * Set identifiantReprise
     *
     * @param string $identifiantReprise
     * @return self
     */
    public function setIdentifiantReprise($identifiantReprise) {
        $this->identifiantReprise = $identifiantReprise;
        return $this;
    }

    /**
     * Get identifiantReprise
     *
     * @return string $identifiantReprise
     */
    public function getIdentifiantReprise() {
        return $this->identifiantReprise;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Remove contrat
     *
     * @param AppBundle\Document\Contrat $contrat
     */
    public function removeContrat(\AppBundle\Document\Contrat $contrat) {
        $this->contrats->removeElement($contrat);
    }

    /**
     * Set contactCoordonnee
     *
     * @param AppBundle\Document\ContactCoordonnee $contactCoordonnee
     * @return self
     */
    public function setContactCoordonnee(\AppBundle\Document\ContactCoordonnee $contactCoordonnee) {
        $this->contactCoordonnee = $contactCoordonnee;
        return $this;
    }

    /**
     * Get contactCoordonnee
     *
     * @return AppBundle\Document\ContactCoordonnee $contactCoordonnee
     */
    public function getContactCoordonnee() {
        return $this->contactCoordonnee;
    }

    /**
     * Set numeroPassageIncrement
     *
     * @param increment $numeroPassageIncrement
     * @return self
     */
    public function setNumeroPassageIncrement($numeroPassageIncrement) {
        $this->numeroPassageIncrement = $numeroPassageIncrement;
        return $this;
    }

    /**
     * Get numeroPassageIncrement
     *
     * @return increment $numeroPassageIncrement
     */
    public function getNumeroPassageIncrement() {
        return $this->numeroPassageIncrement;
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
}

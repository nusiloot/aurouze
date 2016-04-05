<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Etablissement
 *
 * @author mathurin
 */

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Model\EtablissementInfosInterface;
use AppBundle\Document\Adresse;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Document\Contrat;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\EtablissementRepository")
 */
class Etablissement implements EtablissementInfosInterface {

    const PREFIX = "ETABLISSEMENT";

    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\string
     */
    protected $identifiant;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Societe", inversedBy="etablissements")
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

     /**
     * @MongoDB\String
     */
    protected $identifiantReprise;

    /***
     *  @MongoDB\ReferenceMany(targetDocument="Contrat", mappedBy="etablissement")
     */
    protected $contrats = array();


    /**
     * @MongoDB\String
     */
    protected $commentaire;

    public function __construct() {
        $this->adresse = new Adresse();
    }

    public function generateId() {

        $this->setId(self::PREFIX . '-' . $this->identifiant);
    }

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
    public function setRaisonSociale($raisonSociale)
    {
        $this->raisonSociale = $raisonSociale;
        return $this;
    }

    /**
     * Get raisonSociale
     *
     * @return string $raisonSociale
     */
    public function getRaisonSociale()
    {
        return $this->raisonSociale;
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
     * Get societe
     *
     * @return AppBundle\Document\Societe $societe
     */
    public function getSociete()
    {
        return $this->societe;
    }

    /**
     * Set identifiantReprise
     *
     * @param string $identifiantReprise
     * @return self
     */
    public function setIdentifiantReprise($identifiantReprise)
    {
        $this->identifiantReprise = $identifiantReprise;
        return $this;
    }

    /**
     * Get identifiantReprise
     *
     * @return string $identifiantReprise
     */
    public function getIdentifiantReprise()
    {
        return $this->identifiantReprise;
    }
}

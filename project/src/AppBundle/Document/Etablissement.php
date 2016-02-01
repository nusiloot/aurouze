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
use AppBundle\Manager\EtablissementManager as EtablissementManager;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\EtablissementRepository")
 */
class Etablissement {

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
     * @MongoDB\string
     */
    protected $identifiant_societe;

    /**
     * @MongoDB\String
     */
    protected $raison_sociale;

    /**
     * @MongoDB\String
     */
    protected $nom;

    /**
     * @MongoDB\String
     */
    protected $nom_contact;

    /**
     * @MongoDB\EmbedOne(targetDocument="Adresse")
     */
    protected $adresse;

    /**
     * @MongoDB\String
     */
    protected $commentaire;

    /**
     * @MongoDB\String
     */
    protected $type_etablissement;

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
    public function setId() {
        $this->id = $this->generateId();
        return $this;
    }

    public function generateId() {
        return self::PREFIX . '-' . $this->identifiant;
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
     * Set nomContact
     *
     * @param string $nomContact
     * @return self
     */
    public function setNomContact($nomContact) {
        $this->nom_contact = $nomContact;
        return $this;
    }

    /**
     * Get nomContact
     *
     * @return string $nomContact
     */
    public function getNomContact() {
        return $this->nom_contact;
    }

    /**
     * Set typeEtablissement
     *
     * @param string $typeEtablissement
     * @return self
     */
    public function setTypeEtablissement($typeEtablissement) {
        $this->type_etablissement = $typeEtablissement;
        return $this;
    }

    /**
     * Get typeEtablissement
     *
     * @return string $typeEtablissement
     */
    public function getTypeEtablissement() {
        return $this->type_etablissement;
    }

    /**
     * Set raisonSociale
     *
     * @param string $raisonSociale
     * @return self
     */
    public function setRaisonSociale($raisonSociale) {
        $this->raison_sociale = $raisonSociale;
        return $this;
    }

    /**
     * Get raisonSociale
     *
     * @return string $raisonSociale
     */
    public function getRaisonSociale() {
        return $this->raison_sociale;
    }

    /**
     * Set adresse
     *
     * @param AppBundle\Document\Adresse $adresse
     * @return self
     */
    public function setAdresse(\AppBundle\Document\Adresse $adresse) {
        $this->adresse = $adresse;
        return $this;
    }

    /**
     * Get adresse
     *
     * @return AppBundle\Document\Adresse $adresse
     */
    public function getAdresse() {
        return $this->adresse;
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
     * Set identifiantSociete
     *
     * @param string $identifiantSociete
     * @return self
     */
    public function setIdentifiantSociete($identifiantSociete) {
        $this->identifiant_societe = $identifiantSociete;
        return $this;
    }

    /**
     * Get identifiantSociete
     *
     * @return string $identifiantSociete
     */
    public function getIdentifiantSociete() {
        return $this->identifiant_societe;
    }
    
    /**
     * Get adressecomplete
     *
     * @return string $adressecomplete
     */
    public function getAdressecomplete() {
        return $this->adresse->getAdressecomplete();
    }
    
     /**
     * Get iconTypeEtb
     *
     * @return string $adressecomplete
     */
    public function getIconTypeEtb() {
        return EtablissementManager::$type_etablissements_pictos[ $this->type_etablissement];
    }

}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Document;

/**
 * Description of TechnicienInfos
 *
 * @author mathurin
 */
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Manager\UserManager;
/**
 * @MongoDB\EmbeddedDocument
 */
class UserInfos {

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
     * Set prenom
     *
     * @param string $prenom
     * @return self
     */
    public function setPrenom($prenom) {
        $this->prenom = $prenom;
        return $this;
    }

    /**
     * Get prenom
     *
     * @return string $prenom
     */
    public function getPrenom() {
        return $this->prenom;
    }

    /**
     * Set identite
     *
     * @param string $identite
     * @return self
     */
    public function setIdentite($identite) {
        $this->identite = $identite;
        return $this;
    }

    /**
     * Get identite
     *
     * @return string $identite
     */
    public function getIdentite() {
        return $this->identite;
    }

    /**
     * Set couleur
     *
     * @param string $couleur
     * @return self
     */
    public function setCouleur($couleur) {
        $this->couleur = $couleur;
        return $this;
    }

    /**
     * Get couleur
     *
     * @return string $couleur
     */
    public function getCouleur() {
        return $this->couleur;
    }

    public function copyFromUser($user) {
        $this->setIdentifiant($user->getCouleur());
        $this->setIdentite($user->getIdentite());
        $this->setNom($user->getNom());
        $this->setPrenom($user->getPrenom());
        $this->setCouleur($user->getCouleur());
    }
    /**
     * Get getCouleurForLabel
     *
     * @return string 
     */
    public function getCouleurForLabel() {
        return UserManager::$couleur_for_label[$this->getCouleur()];
    }

}

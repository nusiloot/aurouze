<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Document;

/**
 * Description of Etablissement
 *
 * @author mathurin
 */

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

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
    protected $identifiantSociete;

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
     * @MongoDB\String
     */
    protected $adresse;

    /**
     * @MongoDB\String
     */
    protected $code_postal;

    /**
     * @MongoDB\String
     */
    protected $commune;

    /**
     * @MongoDB\String
     */
    protected $telephone_portable;

    /**
     * @MongoDB\String
     */
    protected $telephone_fixe;
    
     /**
     * @MongoDB\String
     */
    protected $fax;
    
    /**
     * @MongoDB\String
     */
    protected $type_etablissement;
    
    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set id
     *
     * @return id $id
     */
    public function setId()
    {
        $this->id = $this->generateId();
    }
    
    public function generateId() {        
        return self::PREFIX.'-'.$this->identifiant;
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
     * Set nomContact
     *
     * @param string $nomContact
     * @return self
     */
    public function setNomContact($nomContact)
    {
        $this->nom_contact = $nomContact;
        return $this;
    }

    /**
     * Get nomContact
     *
     * @return string $nomContact
     */
    public function getNomContact()
    {
        return $this->nom_contact;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return self
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
        return $this;
    }

    /**
     * Get adresse
     *
     * @return string $adresse
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set codePostal
     *
     * @param string $codePostal
     * @return self
     */
    public function setCodePostal($codePostal)
    {
        $this->code_postal = $codePostal;
        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string $codePostal
     */
    public function getCodePostal()
    {
        return $this->code_postal;
    }

    /**
     * Set commune
     *
     * @param string $commune
     * @return self
     */
    public function setCommune($commune)
    {
        $this->commune = $commune;
        return $this;
    }

    /**
     * Get commune
     *
     * @return string $commune
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set telephonePortable
     *
     * @param string $telephonePortable
     * @return self
     */
    public function setTelephonePortable($telephonePortable)
    {
        $this->telephone_portable = $telephonePortable;
        return $this;
    }

    /**
     * Get telephonePortable
     *
     * @return string $telephonePortable
     */
    public function getTelephonePortable()
    {
        return $this->telephone_portable;
    }

    /**
     * Set telephoneFixe
     *
     * @param string $telephoneFixe
     * @return self
     */
    public function setTelephoneFixe($telephoneFixe)
    {
        $this->telephone_fixe = $telephoneFixe;
        return $this;
    }

    /**
     * Get telephoneFixe
     *
     * @return string $telephoneFixe
     */
    public function getTelephoneFixe()
    {
        return $this->telephone_fixe;
    }

    /**
     * Set typeEtablissement
     *
     * @param string $typeEtablissement
     * @return self
     */
    public function setTypeEtablissement($typeEtablissement)
    {
        $this->type_etablissement = $typeEtablissement;
        return $this;
    }

    /**
     * Get typeEtablissement
     *
     * @return string $typeEtablissement
     */
    public function getTypeEtablissement()
    {
        return $this->type_etablissement;
    }

    /**
     * Set identifiantSociete
     *
     * @param string $identifiantSociete
     * @return self
     */
    public function setIdentifiantSociete($identifiantSociete)
    {
        $this->identifiantSociete = $identifiantSociete;
        return $this;
    }

    /**
     * Get identifiantSociete
     *
     * @return string $identifiantSociete
     */
    public function getIdentifiantSociete()
    {
        return $this->identifiantSociete;
    }

    /**
     * Set raisonSociale
     *
     * @param string $raisonSociale
     * @return self
     */
    public function setRaisonSociale($raisonSociale)
    {
        $this->raison_sociale = $raisonSociale;
        return $this;
    }

    /**
     * Get raisonSociale
     *
     * @return string $raisonSociale
     */
    public function getRaisonSociale()
    {
        return $this->raison_sociale;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return self
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * Get fax
     *
     * @return string $fax
     */
    public function getFax()
    {
        return $this->fax;
    }
}

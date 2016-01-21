<?php

namespace AppBundle\Document;

/**
 * AppBundle\Document\PassageEtablissement
 */
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** @MongoDB\EmbeddedDocument */
class PassageEtablissement {

    /**
     * @MongoDB\String
     */
    protected $raison_sociale;

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
    protected $type_etablissement;

    /**
     * Set etablissement
     *
     * @param AppBundle\Document\PassageEtablissement $etablissement
     * @return self
     */
    public function setEtablissement(\AppBundle\Document\PassageEtablissement $etablissement) {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Get etablissement
     *
     * @return AppBundle\Document\PassageEtablissement $etablissement
     */
    public function getEtablissement() {
        return $this->etablissement;
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
     * Set adresse
     *
     * @param string $adresse
     * @return self
     */
    public function setAdresse($adresse) {
        $this->adresse = $adresse;
        return $this;
    }

    /**
     * Get adresse
     *
     * @return string $adresse
     */
    public function getAdresse() {
        return $this->adresse;
    }

    /**
     * Set codePostal
     *
     * @param string $codePostal
     * @return self
     */
    public function setCodePostal($codePostal) {
        $this->code_postal = $codePostal;
        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string $codePostal
     */
    public function getCodePostal() {
        return $this->code_postal;
    }

    /**
     * Set commune
     *
     * @param string $commune
     * @return self
     */
    public function setCommune($commune) {
        $this->commune = $commune;
        return $this;
    }

    /**
     * Get commune
     *
     * @return string $commune
     */
    public function getCommune() {
        return $this->commune;
    }

    /**
     * Set telephonePortable
     *
     * @param string $telephonePortable
     * @return self
     */
    public function setTelephonePortable($telephonePortable) {
        $this->telephone_portable = $telephonePortable;
        return $this;
    }

    /**
     * Get telephonePortable
     *
     * @return string $telephonePortable
     */
    public function getTelephonePortable() {
        return $this->telephone_portable;
    }

    /**
     * Set telephoneFixe
     *
     * @param string $telephoneFixe
     * @return self
     */
    public function setTelephoneFixe($telephoneFixe) {
        $this->telephone_fixe = $telephoneFixe;
        return $this;
    }

    /**
     * Get telephoneFixe
     *
     * @return string $telephoneFixe
     */
    public function getTelephoneFixe() {
        return $this->telephone_fixe;
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
}

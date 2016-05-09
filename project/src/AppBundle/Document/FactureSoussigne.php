<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Document\Adresse;

/**
 * @MongoDB\EmbeddedDocument
*/
class FactureSoussigne  {

    /**
     * @MongoDB\String
     */
    protected $nom;

    /**
     * @MongoDB\String
     */
    protected $adresse;

    /**
     * @MongoDB\String
     */
    protected $codePostal;

    /**
     * @MongoDB\String
     */
    protected $commune;

    /**
     * @MongoDB\String
     */
    protected $codeComptable;

    /**
     * @MongoDB\String
     */
    protected $tvaIntracommunautaire;

    /**
     * @MongoDB\String
     */
    protected $telephone;

    /**
     * @MongoDB\String
     */
    protected $fax;

    /**
     * @MongoDB\String
     */
    protected $email;


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
     * Set codeComptable
     *
     * @param string $codeComptable
     * @return self
     */
    public function setCodeComptable($codeComptable)
    {
        $this->codeComptable = $codeComptable;
        return $this;
    }

    /**
     * Get codeComptable
     *
     * @return string $codeComptable
     */
    public function getCodeComptable()
    {
        return $this->codeComptable;
    }

    /**
     * Set tvaIntracommunautaire
     *
     * @param string $tvaIntracommunautaire
     * @return self
     */
    public function setTvaIntracommunautaire($tvaIntracommunautaire)
    {
        $this->tvaIntracommunautaire = $tvaIntracommunautaire;
        return $this;
    }

    /**
     * Get tvaIntracommunautaire
     *
     * @return string $tvaIntracommunautaire
     */
    public function getTvaIntracommunautaire()
    {
        return $this->tvaIntracommunautaire;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return self
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * Get telephone
     *
     * @return string $telephone
     */
    public function getTelephone()
    {
        return $this->telephone;
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

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
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
        $this->codePostal = $codePostal;
        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string $codePostal
     */
    public function getCodePostal()
    {
        return $this->codePostal;
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
}

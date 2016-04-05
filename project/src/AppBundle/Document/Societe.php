<?php

namespace AppBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\SocieteRepository")
 */
class Societe {

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\SocieteGenerator"})
     */
    protected $id;

    /**
     * @MongoDB\string
     */
    protected $identifiant;

    /**
     * @MongoDB\String
     */
    protected $raisonSociale;

    /**
     * @MongoDB\Boolean
     */
    protected $sousTraitant;

    /**
     * @MongoDB\String
     */
    protected $commentaire;

    /**
     * @MongoDB\String
     */
    protected $type;

    /**
     * @MongoDB\String
     */
    protected $codeComptable;

    /**
     * @MongoDB\EmbedOne(targetDocument="Adresse")
     */
    protected $adresse;


     /**
     * @MongoDB\String
     */
    protected $identifiantReprise;

    /**
    * @MongoDB\Increment
    */
    protected $etablissementIncrement;

    /**
    * @MongoDB\Increment
    */
    protected $contratIncrement;

     /***
     *  @MongoDB\ReferenceMany(targetDocument="Etablissement", mappedBy="societe")
     */
    protected $etablissements = array();

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
     * Set sousTraitant
     *
     * @param boolean $sousTraitant
     * @return self
     */
    public function setSousTraitant($sousTraitant)
    {
        $this->sousTraitant = $sousTraitant;
        return $this;
    }

    /**
     * Get sousTraitant
     *
     * @return boolean $sousTraitant
     */
    public function getSousTraitant()
    {
        return $this->sousTraitant;
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
     * Set adresse
     *
     * @param $adresse
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
     * @return AppBundle\Document\Adresse $adresse
     */
    public function getAdresse()
    {
        return $this->adresse;
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


    /**
     * Set etablissementIncrement
     *
     * @param increment $etablissementIncrement
     * @return self
     */
    public function setEtablissementIncrement($etablissementIncrement)
    {
        $this->etablissementIncrement = $etablissementIncrement;
        return $this;
    }

    /**
     * Get etablissementIncrement
     *
     * @return increment $etablissementIncrement
     */
    public function getEtablissementIncrement()
    {
        return $this->etablissementIncrement;
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
}

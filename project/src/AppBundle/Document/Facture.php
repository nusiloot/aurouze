<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\FactureRepository")
 */
class Facture {

    const PREFIX = "FACTURE";

    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Etablissement", inversedBy="contrats")
     */
    protected $etablissement;

    /**
     * @MongoDB\String
     */
    protected $etablissementIdentifiant;

    /**
     * @MongoDB\Date
     */
    protected $date;

    /**
     * @MongoDB\EmbedMany(targetDocument="FactureLigne")
     */
    protected $lignes;

    public function __construct()
    {
        $this->lignes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function generateId() {

        $this->setId(self::PREFIX . '-' . $this->getEtablissementIdentifiant() .'-' . $this->getDate()->format('Ymd'));
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
     * Add ligne
     *
     * @param AppBundle\Document\FactureLigne $ligne
     */
    public function addLigne(\AppBundle\Document\FactureLigne $ligne)
    {
        $this->lignes[] = $ligne;
    }

    /**
     * Remove ligne
     *
     * @param AppBundle\Document\FactureLigne $ligne
     */
    public function removeLigne(\AppBundle\Document\FactureLigne $ligne)
    {
        $this->lignes->removeElement($ligne);
    }

    /**
     * Get lignes
     *
     * @return \Doctrine\Common\Collections\Collection $lignes
     */
    public function getLignes()
    {
        return $this->lignes;
    }

    /**
     * Set etablissement
     *
     * @param AppBundle\Document\Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(\AppBundle\Document\Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
        $this->setEtablissementIdentifiant($etablissement->getIdentifiant());

        return $this;
    }

    /**
     * Get etablissement
     *
     * @return AppBundle\Document\Etablissement $etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set etablissementIdentifiant
     *
     * @param string $etablissementIdentifiant
     * @return self
     */
    public function setEtablissementIdentifiant($etablissementIdentifiant)
    {
        $this->etablissementIdentifiant = $etablissementIdentifiant;
        return $this;
    }

    /**
     * Get etablissementIdentifiant
     *
     * @return string $etablissementIdentifiant
     */
    public function getEtablissementIdentifiant()
    {
        return $this->etablissementIdentifiant;
    }
}

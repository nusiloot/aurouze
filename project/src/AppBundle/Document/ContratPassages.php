<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Passage;

/**
 * @MongoDB\EmbeddedDocument
 */
class ContratPassages {

    /**
     * @MongoDB\ReferenceOne(targetDocument="Etablissement")
     */
    protected $etablissement;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Passage")
     */
    protected $passages;

    public function __construct() {
        $this->passages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set etablissement
     *
     * @param AppBundle\Document\Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(\AppBundle\Document\Etablissement $etablissement) {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Get etablissement
     *
     * @return AppBundle\Document\Etablissement $etablissement
     */
    public function getEtablissement() {
        return $this->etablissement;
    }

    /**
     * Add passage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function addPassage(\AppBundle\Document\Passage $passage) {
        $this->passages[] = $passage;
    }

    /**
     * Remove passage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function removePassage(\AppBundle\Document\Passage $passage) {
        $this->passages->removeElement($passage);
    }

    /**
     * Get passages
     *
     * @return \Doctrine\Common\Collections\Collection $passages
     */
    public function getPassages() {
        return $this->passages;
    }

    public function getNbPassagePrevu() {
        return count($this->getPassages());
    }

    public function getNbPassagesRealises() {
        $realises = 0;
        foreach ($this->getPassages() as $passage) {
            $realises+=($passage->isRealise());
        }
        return $realises;
    }

    public function getPassagesSorted() {
        $passagesSorted = array();

        foreach ($this->getPassages() as $passage) {
            $passagesSorted[$passage->getId()] = $passage;
        }

        krsort($passagesSorted);

        return $passagesSorted;
    }

}

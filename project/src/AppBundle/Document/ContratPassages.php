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
     * @MongoDB\ReferenceOne(targetDocument="Etablissement", simple=true)
     */
    protected $etablissement;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Passage", simple=true)
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

    public function getPassagesDateSorted($inversed = false) {
    	$passages = $this->passages->toArray();
    	$cmp = ($inversed)? "cmpInvPassage" : "cmpPassage";
    	usort($passages, array("AppBundle\Document\ContratPassages", $cmp));
    	return $passages;
    }

    public static function cmpPassage($a, $b)
    {
    	$da = ($a->getDatePrevision())? $a->getDatePrevision()->format('Ymd') : 0;
    	$db = ($b->getDatePrevision())? $b->getDatePrevision()->format('Ymd') : 0;
    	if ($da == $db) {
    		return 0;
    	}
    	return ($da > $db) ? +1 : -1;
    }

    public static function cmpInvPassage($a, $b)
    {
    	$da = ($a->getDatePrevision())? $a->getDatePrevision()->format('Ymd') : 0;
    	$db = ($b->getDatePrevision())? $b->getDatePrevision()->format('Ymd') : 0;
    	if ($da == $db) {
    		return 0;
    	}
    	return ($da > $db) ? -1 : +1;
    }

    public function getNbPassagePrevu() {
        $nbPrevus = 0;
        foreach ($this->getPassages() as $passage){
            if($passage->isSousContrat()){
                $nbPrevus ++;
            }
        }
        return $nbPrevus;
    }

    public function getNbPassagesRealises() {
        $realises = 0;
        foreach ($this->getPassages() as $passage) {
            $realises+=($passage->isRealise());
        }
        return $realises;
    }

    public function getNbPassagesRealisesOuAnnule() {
        $realisesOuAnnules = 0;
        foreach ($this->getPassages() as $passage) {
            $realisesOuAnnules+=($passage->isRealise() || $passage->isAnnule());
        }
        return $realisesOuAnnules;
    }

    public function getPassagesSorted($reverse = false) {
        $passagesSorted = array();

        foreach ($this->getPassages() as $passage) {
            $passagesSorted[$passage->getId()] = $passage;
        }
        if ($reverse) {
            krsort($passagesSorted);
        } else {
            ksort($passagesSorted);
        }
        return $passagesSorted;
    }
    public function hasEnCoursPassages() {
        foreach ($this->getPassagesSorted() as $passage) {
            if(!$passage->isRealise() && !$passage->isAnnule()){
                return true;
            }
        }
        return false;
    }

}

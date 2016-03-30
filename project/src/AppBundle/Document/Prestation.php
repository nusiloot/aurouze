<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Behat\Transliterator\Transliterator;

/**
 * @MongoDB\EmbeddedDocument
 */
class Prestation {

    /**
     * @MongoDB\String
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $nom;

    /**
     * @MongoDB\String
     */
    protected $nbPassages;

    /**
     * Set id
     *
     * @param string $id
     * @return self
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return self
     */
    public function setNom($nom) {
        $this->nom = $nom;
        $this->setId(strtoupper(Transliterator::urlize($nom)));
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
     * Set nbPassages
     *
     * @param string $nbPassages
     * @return self
     */
    public function setNbPassages($nbPassages) {
        $this->nbPassages = $nbPassages;
        return $this;
    }

    /**
     * Get nbPassages
     *
     * @return string $nbPassages
     */
    public function getNbPassages() {
        return $this->nbPassages;
    }

    public function getNomToString() {
        $mot_inutiles = array('DERATISATION', 'RONGEURS', 'DESINSECTISATION', 'INSECTES', 'RAMPANTS');
        $nom_libelles = explode('-', $this->nom);
        return $this->echapLibelles($nom_libelles, $mot_inutiles);
    }

    public function getWordToPicto() {
        $nom_libelles = explode('-', $this->getId());
        
        $mot_rongeur = array('DERATISATION', 'RONGEURS');
        $mot_puce = array('PUCES', 'ACARIENS');
        $mot_moustique = array('VOLANTS');
        $mot_cafard = array('BLATTES', 'PUNAISES');
        $mot_chenille = array('CHENILLES');
        $mot_pigeon = array('DEPIGEONNAGE');
        
        if($this->isPictoForLibelles($nom_libelles, $mot_rongeur)){
            return 'rongeur';
        }elseif($this->isPictoForLibelles($nom_libelles, $mot_puce)){
            return 'puce';
        }elseif($this->isPictoForLibelles($nom_libelles, $mot_moustique)){
            return 'moustique';
        }elseif($this->isPictoForLibelles($nom_libelles, $mot_cafard)){
            return 'cafard';
        }elseif($this->isPictoForLibelles($nom_libelles, $mot_chenille)){
            return 'chenille';
        }elseif($this->isPictoForLibelles($nom_libelles, $mot_chenille)){
            return 'chenille';
        }
        return false;
    }

    private function echapLibelles($nom_libelles, $mot_inutiles) {
        $str = "";
        foreach ($nom_libelles as $libelle) {
            if (trim($libelle) && !in_array(trim($libelle), $mot_inutiles)) {
                $str .= ' ' . trim($libelle);
            }
        }
        return trim($str);
    }
    
    private function isPictoForLibelles($nom_libelles, $mot_picto) {
       
        foreach ($nom_libelles as $libelle) {
            if (trim($libelle) && in_array(trim($libelle), $mot_picto)) {
                return true;
            }
        }
        return false;
    }

}

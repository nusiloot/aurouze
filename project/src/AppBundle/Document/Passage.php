<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Manager\PassageManager;
use AppBundle\Document\EtablissementInfos;
use AppBundle\Model\DocumentEtablissementInterface;
use AppBundle\Model\DocumentSocieteInterface;
use AppBundle\Document\Prestation;
use AppBundle\Document\Produit;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\PassageRepository") @HasLifecycleCallbacks
 */
class Passage implements DocumentEtablissementInterface, DocumentSocieteInterface {

    const PREFIX = "PASSAGE";

    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $identifiant;

    /**
     * @MongoDB\EmbedMany(targetDocument="Prestation")
     */
    protected $prestations;

    /**
     * @MongoDB\EmbedMany(targetDocument="Produit")
     */
    protected $produits;

    /**
     * @MongoDB\String
     */
    protected $numeroPassageIdentifiant;

    /**
     * @MongoDB\String
     */
    protected $societeIdentifiant;

    /**
     * @MongoDB\Date
     */
    protected $datePrevision;

    /**
     * @MongoDB\Date
     */
    protected $dateDebut;

    /**
     * @MongoDB\Date
     */
    protected $dateFin;
    
    /**
     * @MongoDB\Date
     */
    protected $dateRealise;

    /**
     * @MongoDB\String
     */
    protected $etablissementIdentifiant;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Etablissement", inversedBy="passages")
     */
    protected $etablissement;

    /**
     * @MongoDB\EmbedOne(targetDocument="AppBundle\Document\EtablissementInfos")
     */
    protected $etablissementInfos;

    /**
     * @MongoDB\String
     */
    protected $libelle;

    /**
     * @MongoDB\String
     */
    protected $description;

    /**
     * @MongoDB\ReferenceMany(targetDocument="User", inversedBy="techniciens")
     */
    protected $techniciens;

    /**
     * @MongoDB\String
     */
    protected $statut;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Contrat")
     */
    protected $contrat;

    /**
     * @MongoDB\String
     */
    protected $numeroContratArchive;

    /**
     * @MongoDB\Boolean
     */
    protected $mouvement_declenchable;

    /**
     * @MongoDB\Boolean
     */
    protected $mouvement_declenche;

    public function __construct() {
        $this->etablissementInfos = new EtablissementInfos();
        $this->prestations = new ArrayCollection();
        $this->mouvement_declenchable = false;
        $this->mouvement_declenche = false;
    }

    public function generateId($fromImport = false) {
        if (!$this->getDatePrevision()) {
            $this->setDatePrevision(new \DateTime());
        }

        $this->identifiant = $this->getDatePrevision()->format('Ymd') . '-' . $this->numeroPassageIdentifiant;
        $this->setId(self::PREFIX . '-' . $this->etablissementIdentifiant . '-' . $this->identifiant);
    }

    public function getDescriptionTransformed() {
        return str_replace('\n', "\n", $this->description);
    }

    public function getDuree() {
        if (!$this->dateFin || !$this->dateDebut) {
            return null;
        }
        $interval = $this->dateFin->diff($this->dateDebut);
        return $interval->format('%Hh%I');
    }

    public function isRealise() {
        return $this->statut == PassageManager::STATUT_REALISE;
    }

    public function isPlanifie() {
        return $this->statut == PassageManager::STATUT_PLANIFIE;
    }

    public function isAPlanifie() {
        return $this->statut == PassageManager::STATUT_A_PLANIFIER;
    }

    public function isEnAttente() {
        return $this->statut == PassageManager::STATUT_EN_ATTENTE;
    }

    /** @MongoDB\PreUpdate */
    public function preUpdate() {
        $this->updateStatut();
    }

    /** @MongoDB\PrePersist */
    public function prePersist() {
        $this->updateStatut();
    }

    public function updateStatut() {
        if ($this->getDatePrevision() && !boolval($this->getDateFin()) && !boolval($this->getDateDebut())) {
            $this->setStatut(PassageManager::STATUT_EN_ATTENTE);
            return;
        }
        if (boolval($this->getDateDebut()) && !boolval($this->getDateFin())) {
            $this->setStatut(PassageManager::STATUT_A_PLANIFIER);
            return;
        }
        if (boolval($this->getDateDebut()) && boolval($this->getDateFin()) && !boolval($this->getDateRealise())) {
            $this->setStatut(PassageManager::STATUT_PLANIFIE);
            return;
        }
        if (boolval($this->getDateRealise())) {
            $this->setStatut(PassageManager::STATUT_REALISE);
        }
    }

    public function getIntitule() {

        return $this->getEtablissementInfos()->getIntitule();
    }

    public function getDureePrevisionnelle() {

        return '01:00';
    }

    public function getPassageIdentifiant() {
        return $this->etablissementIdentifiant . '-' . $this->identifiant;
    }

    public function getSociete() {

        return $this->getEtablissement()->getSociete();
    }

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
     * Set numeroPassageIdentifiant
     *
     * @param string $numeroPassageIdentifiant
     * @return self
     */
    public function setNumeroPassageIdentifiant($numeroPassageIdentifiant) {
        $this->numeroPassageIdentifiant = $numeroPassageIdentifiant;
        return $this;
    }

    /**
     * Get numeroPassageIdentifiant
     *
     * @return string $numeroPassageIdentifiant
     */
    public function getNumeroPassageIdentifiant() {
        return $this->numeroPassageIdentifiant;
    }

    /**
     * Set societeIdentifiant
     *
     * @param string $societeIdentifiant
     * @return self
     */
    public function setSocieteIdentifiant($societeIdentifiant) {
        $this->societeIdentifiant = $societeIdentifiant;
        return $this;
    }

    /**
     * Get societeIdentifiant
     *
     * @return string $societeIdentifiant
     */
    public function getSocieteIdentifiant() {
        return $this->societeIdentifiant;
    }

    /**
     * Set dateDebut
     *
     * @param date $dateDebut
     * @return self
     */
    public function setDateDebut($dateDebut) {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return date $dateDebut
     */
    public function getDateDebut() {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param date $dateFin
     * @return self
     */
    public function setDateFin($dateFin) {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * Get dateFin
     *
     * @return date $dateFin
     */
    public function getDateFin() {
        return $this->dateFin;
    }

    /**
     * Set etablissementIdentifiant
     *
     * @param string $etablissementIdentifiant
     * @return self
     */
    public function setEtablissementIdentifiant($etablissementIdentifiant) {
        $this->etablissementIdentifiant = $etablissementIdentifiant;
        return $this;
    }

    /**
     * Get etablissementIdentifiant
     *
     * @return string $etablissementIdentifiant
     */
    public function getEtablissementIdentifiant() {
        return $this->etablissementIdentifiant;
    }

    /**
     * Set etablissementInfos
     *
     * @param EtablissementInfos $etablissementInfos
     * @return self
     */
    public function setEtablissementInfos(EtablissementInfos $etablissementInfos) {
        $this->etablissementInfos = $etablissementInfos;
        return $this;
    }

    /**
     * Get etablissementInfos
     *
     * @return EtablissementInfos $etablissementInfos
     */
    public function getEtablissementInfos() {
        return $this->etablissementInfos;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return self
     */
    public function setLibelle($libelle) {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * Get libelle
     *
     * @return string $libelle
     */
    public function getLibelle() {
        return $this->libelle;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set statut
     *
     * @param string $statut
     * @return self
     */
    public function setStatut($statut) {
        $this->statut = $statut;
        return $this;
    }

    /**
     * Get statut
     *
     * @return string $statut
     */
    public function getStatut() {
        return $this->statut;
    }

    /**
     * Set datePrevision
     *
     * @param date $datePrevision
     * @return self
     */
    public function setDatePrevision($datePrevision) {
        $this->datePrevision = $datePrevision;
        return $this;
    }

    /**
     * Get datePrevision
     *
     * @return date $datePrevision
     */
    public function getDatePrevision() {
        return $this->datePrevision;
    }

    /**
     * Set mouvementDeclenchable
     *
     * @param boolean $mouvementDeclenchable
     * @return self
     */
    public function setMouvementDeclenchable($mouvementDeclenchable) {
        $this->mouvement_declenchable = $mouvementDeclenchable;
        return $this;
    }

    /**
     * Get mouvementDeclenchable
     *
     * @return boolean $mouvementDeclenchable
     */
    public function getMouvementDeclenchable() {
        return $this->mouvement_declenchable;
    }

    /**
     * Set mouvementDeclenche
     *
     * @param boolean $mouvementDeclenche
     * @return self
     */
    public function setMouvementDeclenche($mouvementDeclenche) {
        $this->mouvement_declenche = $mouvementDeclenche;
        return $this;
    }

    /**
     * Get mouvementDeclenche
     *
     * @return boolean $mouvementDeclenche
     */
    public function getMouvementDeclenche() {
        return $this->mouvement_declenche;
    }

    /**
     * Set contrat
     *
     * @param AppBundle\Document\Contrat $contrat
     * @return self
     */
    public function setContrat(\AppBundle\Document\Contrat $contrat) {
        $this->contrat = $contrat;
        return $this;
    }

    /**
     * Get contrat
     *
     * @return AppBundle\Document\Contrat $contrat
     */
    public function getContrat() {
        return $this->contrat;
    }

    public function pullEtablissementInfos() {
        $this->getEtablissementInfos()->pull($this->getEtablissement());
    }

    /**
     * Set etablissement
     *
     * @param AppBundle\Document\Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(\AppBundle\Document\Etablissement $etablissement) {
        $this->etablissement = $etablissement;
        $this->setEtablissementIdentifiant($etablissement->getIdentifiant());
        $this->pullEtablissementInfos();
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
     * Set numeroContratArchive
     *
     * @param string $numeroContratArchive
     * @return self
     */
    public function setNumeroContratArchive($numeroContratArchive) {
        $this->numeroContratArchive = $numeroContratArchive;
        return $this;
    }

    /**
     * Get numeroContratArchive
     *
     * @return string $numeroContratArchive
     */
    public function getNumeroContratArchive() {
        return $this->numeroContratArchive;
    }


    /**
     * Add technicien
     *
     * @param AppBundle\Document\User $technicien
     */
    public function addTechnicien(\AppBundle\Document\User $technicien)
    {
        $this->techniciens[] = $technicien;
    }

    /**
     * Get techniciens
     *
     * @return \Doctrine\Common\Collections\Collection $techniciens
     */
    public function getTechniciens()
    {
        return $this->techniciens;
    }



    /**
     * Add prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function addPrestation(\AppBundle\Document\Prestation $prestation)
    {
        $this->prestations[] = $prestation;
    }

    /**
     * Remove prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function removePrestation(\AppBundle\Document\Prestation $prestation)
    {
        $this->prestations->removeElement($prestation);
    }

   /**
     * Get prestations
     *
     * @return \Doctrine\Common\Collections\Collection $prestations
     */
    public function getPrestations()
    {
        return $this->prestations;
    }

    /**
     * Remove technicien
     *
     * @param AppBundle\Document\User $technicien
     */
    public function removeTechnicien(\AppBundle\Document\User $technicien)
    {
        $this->techniciens->removeElement($technicien);
    }

    public function setTimeDebut($time) {
    	$dateTime = $this->getDateDebut();
    	$this->setDateDebut(new \DateTime($dateTime->format('Y-m-d').'T'.$time.':00'));
    }

    public function setTimeFin($time) {
    	$dateTime = $this->getDateFin();
    	$this->setDateFin(new \DateTime($dateTime->format('Y-m-d').'T'.$time.':00'));
    }

    public function getTimeDebut() {
    	$dateTime = $this->getDateDebut();
    	return $dateTime->format('H:i');
    }

    public function getTimeFin() {
    	$dateTime = $this->getDateFin();
    	return $dateTime->format('H:i');
    }

    /**
     * Add produit
     *
     * @param AppBundle\Document\Produit $produit
     */
    public function addProduit(\AppBundle\Document\Produit $produit)
    {
        $this->produits[] = $produit;
    }

    /**
     * Remove produit
     *
     * @param AppBundle\Document\Produit $produit
     */
    public function removeProduit(\AppBundle\Document\Produit $produit)
    {
        $this->produits->removeElement($produit);
    }

    /**
     * Get produits
     *
     * @return \Doctrine\Common\Collections\Collection $produits
     */
    public function getProduits()
    {
        return $this->produits;
    }
    
    public function getStatutLibelle() {
        return PassageManager::$statutsLibelles[$this->getStatut()];
    }
    public function getStatutLibelleActions() {
        return PassageManager::$statutsLibellesActions[$this->getStatut()];
    }

    /**
     * Set dateRealise
     *
     * @param date $dateRealise
     * @return self
     */
    public function setDateRealise($dateRealise)
    {
        $this->dateRealise = $dateRealise;
        return $this;
    }

    /**
     * Get dateRealise
     *
     * @return date $dateRealise
     */
    public function getDateRealise()
    {
        return $this->dateRealise;
    }
    
    public function isFirstPassageNonRealise() {
        
    }
}

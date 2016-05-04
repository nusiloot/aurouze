<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Compte;
use AppBundle\Document\Prestation;
use AppBundle\Document\Societe;
use AppBundle\Document\ContratPassages;
use AppBundle\Document\Mouvement;
use AppBundle\Model\DocumentFacturableInterface;
use AppBundle\Model\DocumentSocieteInterface;
use AppBundle\Manager\ConfigurationManager;
use AppBundle\Manager\ContratManager;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\ContratRepository") @HasLifecycleCallbacks
 *
 */
class Contrat implements DocumentSocieteInterface,  DocumentFacturableInterface {

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\ContratGenerator"})
     */
    protected $id;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Etablissement", inversedBy="contrats")
     */
    protected $etablissements;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Compte")
     */
    protected $commercial;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Compte")
     */
    protected $technicien;

    /**
     * @MongoDB\EmbedMany(targetDocument="ContratPassages", strategy="set")
     */
    protected $contratPassages;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Societe")
     */
    protected $societe;

    /**
     * @MongoDB\String
     */
    protected $identifiant;

    /**
     * @MongoDB\String
     */
    protected $numeroArchive;

     /**
     * @MongoDB\Boolean
     */
    protected $multiTechnicien;

    /**
     * @MongoDB\String
     */
    protected $typeContrat;

    /**
     * @MongoDB\String
     */
    protected $nomenclature;
    
    /**
     * @MongoDB\String
     */
    protected $commentaire;
    
    /**
     * @MongoDB\String
     */
    protected $markdown;

    /**
     * @MongoDB\EmbedMany(targetDocument="Prestation")
     */
    protected $prestations;

    /**
     * @MongoDB\EmbedMany(targetDocument="Produit")
     */
    protected $produits;

    /**
     * @MongoDB\Date
     */
    protected $dateCreation;

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
    protected $dateAcceptation;

    /**
     * @MongoDB\Date
     */
    protected $dateResiliation;
    
    /**
     * @MongoDB\Int
     */
    protected $duree;

    /**
     * @MongoDB\Int
     */
    protected $dureeGarantie;

    /**
     * @MongoDB\Int
     */
    protected $nbPassages;

    /**
     * @MongoDB\Int
     */
    protected $dureePassage;

    /**
     * @MongoDB\Int
     */
    protected $nbFactures;

    /**
     * @MongoDB\Float
     */
    protected $prixHt;

    /**
     * @MongoDB\EmbedMany(targetDocument="Mouvement")
     */
    protected $mouvements;

    /**
     * @MongoDB\String
     */
    protected $statut;

    /**
     * @MongoDB\String
     */
    protected $identifiantReprise;

     /**
     * @MongoDB\Boolean
     */
    protected $tvaReduite;

     /**
     * @MongoDB\Collection
     */
    protected $moyens;

     /**
     * @MongoDB\String
     */
    protected $conditionsParticulieres;

    public function __construct() {
        $this->etablissements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->prestations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->produits = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mouvements = new ArrayCollection();
        $this->contratPassages = array();
    }

    /**
     * Set commercial
     *
     * @param AppBundle\Document\Compte $commercial
     * @return self
     */
    public function setCommercial(\AppBundle\Document\Compte $commercial) {
        $this->commercial = $commercial;
        return $this;
    }

    /**
     * Get commercial
     *
     * @return AppBundle\Document\Compte $commercial
     */
    public function getCommercial() {
        return $this->commercial;
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
     * Set typeContrat
     *
     * @param string $typeContrat
     * @return self
     */
    public function setTypeContrat($typeContrat) {
        $this->typeContrat = $typeContrat;
        return $this;
    }

    /**
     * Get typeContrat
     *
     * @return string $typeContrat
     */
    public function getTypeContrat() {
        return $this->typeContrat;
    }

    /**
     * Set nomenclature
     *
     * @param string $nomenclature
     * @return self
     */
    public function setNomenclature($nomenclature) {
        $this->nomenclature = $nomenclature;
        return $this;
    }

    /**
     * Get nomenclature
     *
     * @return string $nomenclature
     */
    public function getNomenclature() {
        return $this->nomenclature;
    }

    /**
     * Add prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function addPrestation(\AppBundle\Document\Prestation $prestation) {
        foreach ($this->getPrestations() as $prest) {
            if($prest == $prestation){
                return;
            }
        }
        $this->prestations[] = $prestation;
    }

    /**
     * Remove prestation
     *
     * @param AppBundle\Document\Prestation $prestation
     */
    public function removePrestation(\AppBundle\Document\Prestation $prestation) {
        $this->prestations->removeElement($prestation);
    }

    /**
     * Get prestations
     *
     * @return \Doctrine\Common\Collections\Collection $prestations
     */
    public function getPrestations() {
        return $this->prestations;
    }

    /**
     * Add produit
     *
     * @param AppBundle\Document\Produit $prestation
     */
    public function addProduit(\AppBundle\Document\Produit $produit) {
        foreach ($this->getProduits() as $prod) {
            if($prod == $produit){
                return;
            }
        }
        $this->produits[] = $produit;
    }

    /**
     * Remove produit
     *
     * @param AppBundle\Document\Produit $produit
     */
    public function removeProduit(\AppBundle\Document\Produit $produit) {
        $this->produits->removeElement($produit);
    }

    /**
     * Get produits
     *
     * @return \Doctrine\Common\Collections\Produit $produits
     */
    public function getProduits() {
        return $this->produits;
    }

    /**
     * Set dateCreation
     *
     * @param date $dateCreation
     * @return self
     */
    public function setDateCreation($dateCreation) {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return date $dateCreation
     */
    public function getDateCreation() {
        return $this->dateCreation;
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
     * Set dateAcceptation
     *
     * @param date $dateAcceptation
     * @return self
     */
    public function setDateAcceptation($dateAcceptation) {
        $this->dateAcceptation = $dateAcceptation;
        return $this;
    }

    /**
     * Get dateAcceptation
     *
     * @return date $dateAcceptation
     */
    public function getDateAcceptation() {
        return $this->dateAcceptation;
    }

    /**
     * Set duree
     *
     * @param int $duree
     * @return self
     */
    public function setDuree($duree) {
        $this->duree = $duree;
        return $this;
    }

    /**
     * Get duree
     *
     * @return int $duree
     */
    public function getDuree() {
        return $this->duree;
    }

    /**
     * Set dureeGarantie
     *
     * @param int $dureeGarantie
     * @return self
     */
    public function setDureeGarantie($dureeGarantie) {
        $this->dureeGarantie = $dureeGarantie;
        return $this;
    }

    /**
     * Get dureeGarantie
     *
     * @return int $dureeGarantie
     */
    public function getDureeGarantie() {
        return $this->dureeGarantie;
    }

    /**
     * Set nbPassages
     *
     * @param int $nbPassages
     * @return self
     */
    public function setNbPassages($nbPassages) {
        $this->nbPassages = $nbPassages;
        return $this;
    }

    /**
     * Get nbPassages
     *
     * @return int $nbPassages
     */
    public function getNbPassages() {
        return $this->nbPassages;
    }

    /**
     * Set dureePassage
     *
     * @param int $dureePassage
     * @return self
     */
    public function setDureePassage($dureePassage) {
        $this->dureePassage = $dureePassage;
        return $this;
    }

    /**
     * Get dureePassage
     *
     * @return int $dureePassage
     */
    public function getDureePassage() {
        return $this->dureePassage;
    }

    /**
     * Set nbFactures
     *
     * @param int $nbFactures
     * @return self
     */
    public function setNbFactures($nbFactures) {
        $this->nbFactures = $nbFactures;
        return $this;
    }

    /**
     * Get nbFactures
     *
     * @return int $nbFactures
     */
    public function getNbFactures() {
        return $this->nbFactures;
    }

    /**
     * Set prixHt
     *
     * @param float $prixHt
     * @return self
     */
    public function setPrixHt($prixHt) {
        $this->prixHt = $prixHt;
        return $this;
    }

    /**
     * Get prixHt
     *
     * @return float $prixHt
     */
    public function getPrixHt() {
        return $this->prixHt;
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
     * Add mouvement
     *
     * @param AppBundle\Document\Mouvement $mouvement
     */
    public function addMouvement(\AppBundle\Document\Mouvement $mouvement) {
        $this->mouvements[] = $mouvement;
    }

    /**
     * Remove mouvement
     *
     * @param AppBundle\Document\Mouvement $mouvement
     */
    public function removeMouvement(\AppBundle\Document\Mouvement $mouvement) {
        $this->mouvements->removeElement($mouvement);
    }

    /**
     * Get mouvements
     *
     * @return \Doctrine\Common\Collections\Collection $mouvements
     */
    public function getMouvements() {
        return $this->mouvements;
    }

    public function isTerminee() {

        return ($this->getDateFin() < new \DateTime());
    }

    public function updateObject() {
        if (!$this->getNbPassages()) {
            $max = 0;
            foreach ($this->getPrestations() as $prestation) {
                if ($prestation->getNbPassages() > $max) {
                    $max = $prestation->getNbPassages();
                }
            }
            $this->setNbPassages($max);
        }

    }

    public function updatePrestations($dm){
        $cm = new ConfigurationManager($dm);
        $configuration = $cm->getRepository()->findOneById(Configuration::PREFIX);
        $prestationArray = $configuration->getPrestationsArray();
         foreach ($this->getPrestations() as $prestation) {
            $prestationNom =  $prestationArray[$prestation->getIdentifiant()];
            $prestation->setNom($prestationNom);
        }
    }
    public function updateProduits($dm){
        $cm = new ConfigurationManager($dm);
        $configuration = $cm->getRepository()->findOneById(Configuration::PREFIX);
        $produitsArray = $configuration->getProduitsArray();
         foreach ($this->getProduits() as $produit) {
            $produitConf =  $produitsArray[$produit->getIdentifiant()];
            $produit->setNom($produitConf->getNom());
            $produit->setPrixHt($produitConf->getPrixHt());
            $produit->setPrixPrestation($produitConf->getPrixPrestation());
        }
    }


    public function getHumanDureePassage() {
        $duree = $this->getDureePassage();
        $heure = floor($duree / 60);
        return sprintf('%02d', $heure) . 'h' . sprintf('%02d', ((($duree / 60) - $heure) * 60));
    }

    public function getPrixMouvements() {
        $prix = 0;
        foreach ($this->getMouvements() as $mouvement) {
            $prix = $prix + $mouvement->getPrix();
        }

        return $prix;
    }

    public function getPrixRestant() {
        $prixMouvement = $this->getPrixMouvements();

        return $this->getPrixHt() - $this->getPrixMouvements();
    }

    public function getNbFacturesRestantes() {

        return 1;
    }

    public function generateMouvement() {
        if ($this->getPrixRestant() <= 0 || $this->getNbFacturesRestantes() <= 0) {
            return;
        }
        $mouvement = new Mouvement();
        $mouvement->setPrix(round($this->getPrixRestant() / $this->getNbFacturesRestantes(), 2));
        $mouvement->setFacturable(true);
        $mouvement->setFacture(false);
        $mouvement->setLibelle(sprintf("Facture %s/%s - Proposition n° %s du %s au %s", 1, 2, "0000000000", $this->getDateDebut()->format('m/Y'), $this->getDateFin()->format('m/Y')));

        $this->addMouvement($mouvement);
    }

    public function getPrevisionnel($dateDebut = null) {

        if (!$dateDebut) {
            $dateDebut = new \DateTime();
        }

        $dureeContratMois = $this->getDuree();

        $maxNbPrestations = 0;
        $typePrestationPrincipal = "";
        foreach ($this->getPrestations() as $prestation) {
            if ($prestation->getNbPassages() > $maxNbPrestations) {
                $maxNbPrestations = $prestation->getNbPassages();
                $typePrestationPrincipal = $prestation;
            }
        }
        $passagesDatesArray = array();
        if(!count($this->getPrestations())){
            return $passagesDatesArray;
        }
        $monthInterval = (floatval($dureeContratMois) / floatval($maxNbPrestations));
        $nb_month = intval($monthInterval);
        $dateLastPassage = $dateDebut;
        $passagesDatesArray[$dateLastPassage->format('Y-m-d')] = new \stdClass();
        $passagesDatesArray[$dateLastPassage->format('Y-m-d')]->prestations = array();

        foreach ($this->getPrestations() as $prestation) {
            if ($prestation->getNbPassages() > 0) {
                $passagesDatesArray[$dateLastPassage->format('Y-m-d')]->prestations[] = $prestation;
                $passagesDatesArray[$dateLastPassage->format('Y-m-d')]->mouvement_declenchable = 1;
            }
        }

        for ($index = 1; $index < $maxNbPrestations; $index++) {
            $monthDate = clone $dateLastPassage;
            $nextMonth = $monthDate->modify("+" . $nb_month . " month");
            $nb_days = intval(($monthInterval - $nb_month) * cal_days_in_month(CAL_GREGORIAN, $nextMonth->format('m'), $nextMonth->format('Y')));

            $dateLastPassage = $dateLastPassage->modify("+" . $nb_month . " month")->modify("+" . $nb_days . " day");
            $passagesDatesArray[$dateLastPassage->format('Y-m-d')] = new \stdClass();
            $passagesDatesArray[$dateLastPassage->format('Y-m-d')]->prestations = array();
            $passagesDatesArray[$dateLastPassage->format('Y-m-d')]->prestations[] = $typePrestationPrincipal;
        }

        foreach ($this->getPrestations() as $prestation) {
            if (($prestation->getIdentifiant() != $typePrestationPrincipal->getIdentifiant()) && $prestation->getNbPassages() > 1) {
                $nbPassagesPrestationRestant = $prestation->getNbPassages();
                $nbPassagesRestant = count($passagesDatesArray);
                $occurPassage = (floatval($nbPassagesRestant) / floatval($nbPassagesPrestationRestant));
                $compteurPassage = $occurPassage;
                $cpt = 0;
                foreach ($passagesDatesArray as $date => $passage) {
                    if ($cpt < 1) {
                        $cpt++;
                        continue;
                    }
                    if ($cpt >= $compteurPassage) {
                        $passagesDatesArray[$date]->prestations[] = $prestation;
                        $compteurPassage+=$occurPassage;
                    }
                    $cpt++;
                }
            }
        }

        $facturationInterval = (floatval($maxNbPrestations) / floatval($this->getNbFactures()));
        $compteurFacturation = $facturationInterval;
        $cpt = 0;

        foreach ($passagesDatesArray as $date => $passage) {
            if ($cpt < 1) {
                $cpt++;
                continue;
            }
            if ($cpt >= $compteurFacturation) {
                $passagesDatesArray[$date]->mouvement_declenchable = 1;
                $compteurFacturation+=$facturationInterval;
            } else {
                $passagesDatesArray[$date]->mouvement_declenchable = 0;
            }
            $cpt++;
        }
        return $passagesDatesArray;
    }
    
    public function getTypeContratLibelle() {
        if(!$this->getTypeContrat()){
            return "";
        }
        return ContratManager::$types_contrat_libelles[$this->getTypeContrat()];
    }

    public function getPassagesEtablissementNode(Etablissement $etablissement)
    {
          $contratPassages = $this->getContratPassages();
        if(!isset($contratPassages[$etablissement->getId()])){
            return null;
        }

        return $contratPassages[$etablissement->getId()];
    }

    public function getPassages(Etablissement $etablissement)
    {
        if(!isset($this->contratPassages[$etablissement->getId()])){
            return array();
        }

        return $this->contratPassages[$etablissement->getId()]->getPassagesSorted();
    }


    /**
     * Add etablissement
     *
     * @param AppBundle\Document\Etablissement $etablissement
     */
    public function addEtablissement(\AppBundle\Document\Etablissement $etablissement)
    {
        foreach ($this->getEtablissements() as $etb){
            if($etb->getId() == $etablissement->getId()){
                return;
            }
        }
        $this->etablissements[] = $etablissement;
    }

    /**
     * Remove etablissement
     *
     * @param AppBundle\Document\Etablissement $etablissement
     */
    public function removeEtablissement(\AppBundle\Document\Etablissement $etablissement)
    {
        $this->etablissements->removeElement($etablissement);
    }

    /**
     * Get etablissements
     *
     * @return \Doctrine\Common\Collections\Collection $etablissements
     */
    public function getEtablissements()
    {
        return $this->etablissements;
    }

    /**
     * Set technicien
     *
     * @param AppBundle\Document\Compte $technicien
     * @return self
     */
    public function setTechnicien(\AppBundle\Document\Compte $technicien)
    {
        $this->technicien = $technicien;
        return $this;
    }

    /**
     * Get technicien
     *
     * @return AppBundle\Document\Compte $technicien
     */
    public function getTechnicien()
    {
        return $this->technicien;
    }

    public function changeTechnicien($newTechnicien) {
        if(!$newTechnicien){
            return false;
        }
        $this->setTechnicien($newTechnicien);
        foreach ($this->getContratPassages() as $contratPassage) {
            foreach ($contratPassage->getPassagesSorted() as $passage) {
                if($passage->isEnAttente() || $passage->isAPlanifie()){
                    $passage->removeAllTechniciens();
                    $passage->addTechnicien($newTechnicien);
                }
            }
        }
    }
    
    /**
     * Add contratPassage
     *
     * @param AppBundle\Document\Passage $passage
     */
    public function addPassage(\AppBundle\Document\Etablissement $etablissement, Passage $passage)
    {
        $contratPassagesToSet = new ContratPassages();
        foreach ($this->getContratPassages() as $contratPassages) {
            if($etablissement->getId() == $contratPassages->getEtablissement()->getId()){
                $contratPassagesToSet = $contratPassages;
            }
        }
        $contratPassagesToSet->addPassage($passage);
        $contratPassagesToSet->setEtablissement($etablissement);

        $this->addContratPassage($etablissement,$contratPassagesToSet);
    }

    /**
     * Add contratPassage
     *
     * @param AppBundle\Document\ContratPassages $contratPassage
     */
    public function addContratPassage($etablissement, \AppBundle\Document\ContratPassages $contratPassage)
    {
        $this->contratPassages[$etablissement->getId()] = $contratPassage;
    }

    /**
     * Remove contratPassage
     *
     * @param AppBundle\Document\ContratPassages $contratPassage
     */
    public function removeContratPassage(\AppBundle\Document\ContratPassages $contratPassage)
    {
        $this->contratPassages->removeElement($contratPassage);
    }

    /**
     * Get contratPassages
     *
     * @return \Doctrine\Common\Collections\Collection $contratPassages
     */
    public function getContratPassages()
    {
        return $this->contratPassages;
    }

    /**
     * Set societe
     *
     * @param AppBundle\Document\Societe $societe
     * @return self
     */
    public function setSociete(\AppBundle\Document\Societe $societe)
    {
        $this->societe = $societe;
        return $this;
    }

    /**
     * Get societe
     *
     * @return AppBundle\Document\Societe $societe
     */
    public function getSociete()
    {
        return $this->societe;
    }

    /**
     * Set multiTechnicien
     *
     * @param boolean $multiTechnicien
     * @return self
     */
    public function setMultiTechnicien($multiTechnicien)
    {
        $this->multiTechnicien = $multiTechnicien;
        return $this;
    }

    /**
     * Get multiTechnicien
     *
     * @return boolean $multiTechnicien
     */
    public function getMultiTechnicien()
    {
        return $this->multiTechnicien;
    }

    /**
     * Set numeroArchive
     *
     * @param string $numeroArchive
     * @return self
     */
    public function setNumeroArchive($numeroArchive)
    {
        $this->numeroArchive = $numeroArchive;
        return $this;
    }

    /**
     * Get numeroArchive
     *
     * @return string $numeroArchive
     */
    public function getNumeroArchive()
    {
        return $this->numeroArchive;
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
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set dateResiliation
     *
     * @param date $dateResiliation
     * @return self
     */
    public function setDateResiliation($dateResiliation)
    {
        $this->dateResiliation = $dateResiliation;
        return $this;
    }

    /**
     * Get dateResiliation
     *
     * @return date $dateResiliation
     */
    public function getDateResiliation()
    {
        return $this->dateResiliation;
    }

    /**
     * Set tvaReduite
     *
     * @param boolean $tvaReduite
     * @return self
     */
    public function setTvaReduite($tvaReduite)
    {
        $this->tvaReduite = $tvaReduite;
        return $this;
    }

    /**
     * Get tvaReduite
     *
     * @return boolean $tvaReduite
     */
    public function getTvaReduite()
    {
        return $this->tvaReduite;
        
    }
    
     public function isResilie() {
        return ($this->statut == ContratManager::STATUT_RESILIE);
    }
    
    public function isBrouillon() {
        return ($this->statut == ContratManager::STATUT_BROUILLON);
    }
    
     public function isEnCours() {
        return ($this->statut == ContratManager::STATUT_EN_COURS);
    }
    
     public function isAVenir() {
        return ($this->statut == ContratManager::STATUT_A_VENIR);
    }
    
    public function isEnAttenteAcceptation() {
        return ($this->statut == ContratManager::STATUT_EN_ATTENTE_ACCEPTATION);
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
     * Set markdown
     *
     * @param string $markdown
     * @return self
     */
    public function setMarkdown($markdown)
    {
        $this->markdown = $markdown;
        return $this;
    }

    /**
     * Get markdown
     *
     * @return string $markdown
     */
    public function getMarkdown()
    {
        return $this->markdown;
    }
    
    public function isTacite()
    {
    	return $this->getTypeContrat() == ContratManager::TYPE_CONTRAT_RECONDUCTION_TACITE;
    }
    
    public function getTva()
    {
    	return ($this->getTvaReduite())? 0.1 : 0.2;
    }

    /**
     * Set moyens
     *
     * @param collection $moyens
     * @return self
     */
    public function setMoyens($moyens)
    {
        $this->moyens = $moyens;
        return $this;
    }

    /**
     * Get moyens
     *
     * @return collection $moyens
     */
    public function getMoyens()
    {
        return $this->moyens;
    }

    /**
     * Set conditionsParticulieres
     *
     * @param string $conditionsParticulieres
     * @return self
     */
    public function setConditionsParticulieres($conditionsParticulieres)
    {
        $this->conditionsParticulieres = $conditionsParticulieres;
        return $this;
    }

    /**
     * Get conditionsParticulieres
     *
     * @return string $conditionsParticulieres
     */
    public function getConditionsParticulieres()
    {
        return $this->conditionsParticulieres;
    }
}

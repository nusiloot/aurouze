<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Document\RendezVous;
use AppBundle\Document\Compte;
use AppBundle\Model\DocumentSocieteInterface;
use AppBundle\Model\DocumentPlannifiableInterface;
use AppBundle\Manager\DevisManager;
use AppBundle\Manager\ContratManager;
use Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\DevisRepository") @HasLifecycleCallbacks
 */
class Devis implements DocumentSocieteInterface, DocumentPlannifiableInterface {

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\DevisGenerator"})
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Societe", inversedBy="devis", simple=true)
     */
    protected $societe;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Etablissement", inversedBy="devis", simple=true)
     */
    protected $etablissement;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Compte", inversedBy="devis")
     */
    protected $commercial;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Compte", inversedBy="techniciens", simple=true)
     */
    protected $techniciens;

    /**
     * @MongoDB\EmbedOne(targetDocument="Soussigne")
     */
    protected $emetteur;

    /**
     * @MongoDB\EmbedOne(targetDocument="Soussigne")
     */
    protected $destinataire;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $dateEmission;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $dateDebut;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $dateFin;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $datePrevision;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $dateSignature;

    /**
    * @MongoDB\ReferenceOne(targetDocument="RendezVous", simple=true)
     */
    protected $rendezvous;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $signatureBase64;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $emailTransmission;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $secondEmailTransmission;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $nomTransmission;


    /**
     * @MongoDB\Field(type="float")
     */
    protected $montantHT;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $montantTTC;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $montantTaxe;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $description;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $numeroDevis;

    /**
     * @MongoDB\EmbedMany(targetDocument="LigneFacturable")
     */
    protected $lignes;


    public function __construct() {
        $this->techniciens = new ArrayCollection();
        $this->emetteur = new Soussigne();
        $this->destinataire = new Soussigne();
        $this->dateEmission = new \DateTime();
        $this->datePrevision = new \DateTime();
        if(!$this->getLignes() || !count($this->getLignes())){
          $l = new LigneFacturable();
          $this->addLigne($l);
        }
    }

    /**
     * Set societe
     *
     * @param AppBundle\Document\Societe $societe
     * @return self
     */
    public function setSociete(\AppBundle\Document\Societe $societe, $store = true) {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get societe
     *
     * @return AppBundle\Document\Societe $societe
     */
    public function getSociete() {
        return $this->societe;
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
     * Set commercial
     *
     * @param AppBundle\Document\Compte $commercial
     * @return $this
     */
    public function setCommercial(\AppBundle\Document\Compte $commercial)
    {
        $this->commercial = $commercial;
        return $this;
    }

    /**
     * Get commercial
     *
     * @return AppBundle\Document\Compte $commercial
     */
    public function getCommercial()
    {
        return $this->commercial;
    }

    /**
     * Set emetteur
     *
     * @param AppBundle\Document\Soussigne $emetteur
     * @return self
     */
    public function setEmetteur(\AppBundle\Document\Soussigne $emetteur) {
        $this->emetteur = $emetteur;
        return $this;
    }

    /**
     * Get emetteur
     *
     * @return AppBundle\Document\Soussigne $emetteur
     */
    public function getEmetteur() {
        return $this->emetteur;
    }

    /**
     * Set destinataire
     *
     * @param AppBundle\Document\Soussigne $destinataire
     * @return $this
     */
    public function setDestinataire(\AppBundle\Document\Soussigne $destinataire)
    {
        $this->destinataire = $destinataire;
        return $this;
    }

    /**
     * Get destinataire
     *
     * @return AppBundle\Document\Soussigne $destinataire
     */
    public function getDestinataire()
    {
        return $this->destinataire;
    }

    /**
     * Set dateEmission
     *
     * @param date $dateEmission
     * @return $this
     */
    public function setDateEmission($dateEmission)
    {
        $this->dateEmission = $dateEmission;
        return $this;
    }

    /**
     * Get dateEmission
     *
     * @return date $dateEmission
     */
    public function getDateEmission()
    {
        return $this->dateEmission;
    }


    /**
     * Set dateSignature
     *
     * @param date $dateSignature
     * @return $this
     */
    public function setDateSignature($dateSignature)
    {
        $this->dateSignature = $dateSignature;
        return $this;
    }

    /**
     * Get dateSignature
     *
     * @return date $dateSignature
     */
    public function getDateSignature()
    {
        return $this->dateSignature;
    }

    /**
     * Set montantHT
     *
     * @param float $montantHT
     * @return $this
     */
    public function setMontantHT($montantHT)
    {
        $this->montantHT = $montantHT;
        return $this;
    }

    /**
     * Get montantHT
     *
     * @return float $montantHT
     */
    public function getMontantHT()
    {
        return $this->montantHT;
    }

    /**
     * Set montantTTC
     *
     * @param float $montantTTC
     * @return $this
     */
    public function setMontantTTC($montantTTC)
    {
        $this->montantTTC = $montantTTC;
        return $this;
    }

    /**
     * Get montantTTC
     *
     * @return float $montantTTC
     */
    public function getMontantTTC()
    {
        return $this->montantTTC;
    }

    /**
     * Set montantTaxe
     *
     * @param float $montantTaxe
     * @return $this
     */
    public function setMontantTaxe($montantTaxe)
    {
        $this->montantTaxe = $montantTaxe;
        return $this;
    }

    /**
     * Get montantTaxe
     *
     * @return float $montantTaxe
     */
    public function getMontantTaxe()
    {
        return $this->montantTaxe;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set numeroDevis
     *
     * @param string $numeroDevis
     * @return $this
     */
    public function setNumeroDevis($numeroDevis)
    {
        $this->numeroDevis = $numeroDevis;
        return $this;
    }

    /**
     * Get numeroDevis
     *
     * @return string $numeroDevis
     */
    public function getNumeroDevis()
    {
        return $this->numeroDevis;
    }


    /**
     * Add ligne
     *
     * @param AppBundle\Document\LigneFacturable $ligne
     */
    public function addLigne(\AppBundle\Document\LigneFacturable $ligne)
    {
        $this->lignes[] = $ligne;
    }

    /**
     * Remove ligne
     *
     * @param AppBundle\Document\LigneFacturable $ligne
     */
    public function removeLigne(\AppBundle\Document\LigneFacturable $ligne)
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
     * Set signatureBase64
     *
     * @param string $signatureBase64
     * @return self
     */
    public function setSignatureBase64($signatureBase64)
    {
        $this->signatureBase64 = $signatureBase64;
        return $this;
    }

    /**
     * Get signatureBase64
     *
     * @return string $signatureBase64
     */
    public function getSignatureBase64()
    {
        return $this->signatureBase64;
    }

    public function updateCalcul() {
        $montant = 0;
        $montantTaxe = 0;
        if($this->getLignes()){
          foreach ($this->getLignes() as $ligne) {
              $ligne->update();
              $montant = $montant + $ligne->getMontantHT();
              $montantTaxe = $montantTaxe + $ligne->getMontantTaxe();
          }
        }
        $this->setMontantHT(round($montant, 2));
        $this->setMontantTaxe(round($montantTaxe, 2));
        $this->setMontantTTC(round($montant + $montantTaxe, 2));
    }

    public function update() {
        $this->updateCalcul();
        $this->storeDestinataire();

    }

    public function storeDestinataire() {
        $societe = $this->getSociete();
        $destinataire = $this->getDestinataire();

        $nom = $societe->getRaisonSociale();

        $destinataire->setRaisonSociale($societe->getRaisonSociale());
        $destinataire->setNom($nom);
        $destinataire->setAdresse($societe->getAdresse()->getAdresseFormatee());
        $destinataire->setCodePostal($societe->getAdresse()->getCodePostal());
        $destinataire->setCommune($societe->getAdresse()->getCommune());
        $destinataire->setCodeComptable($societe->getCodeComptable());
    }

    /**
     * Set rendezvous
     *
     * @param RendezVous $rendezvous
     * @return $this
     */
    public function setRendezvous(RendezVous $rendezvous)
    {
        $this->rendezvous = $rendezvous;
        return $this;
    }

    /**
     * Get rendezvous
     *
     * @return RendezVous $rendezvous
     */
    public function getRendezvous()
    {
        return $this->rendezvous;
    }

// A partir de là => a mutualiser avec les passages

    public function getEtablissement()
    {
        return $this->getSociete()->getEtablissements()->first();
    }

    public function getDureePrevisionnelle(){
      return '01:00';
    }

    public function setDureePrevisionnelle($dureePrevisionnelle)
    {
    }

    public function setCommentaire($commentaire){
      return $this->setDescription($commentaire);
    }


    public function isTransmis(){
      return boolval($this->signatureBase64) || boolval($this->emailTransmission);
    }

    public function getEtablissementInfos() {
        return $this->getEtablissement();
    }

    /**
     * Set etablissement
     *
     * @param AppBundle\Document\Etablissement $etablissement
     * @return $this
     */
    public function setEtablissement(\AppBundle\Document\Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Add technicien
     *
     * @param AppBundle\Document\Compte $technicien
     * @return $this
     */
    public function addTechnicien(Compte $technicien)
    {
        if (! $this->techniciens->contains($technicien)) {
            $this->techniciens[] = $technicien;
        }
        return $this;
    }

    /**
     * Get technicien
     *
     * @return Collection $techniciens
     */
    public function getTechniciens()
    {
        return $this->techniciens;
    }

    public function getTechniciensIds() {
        $techniciens = array();

        foreach ($this->getTechniciens() as $technicien) {
            $techniciens[] = $technicien->getId();
        }

        sort($techniciens);

        return $techniciens;
    }

    /**
     * Set datePrevision
     *
     * @param date $datePrevision
     * @return $this
     */
    public function setDatePrevision($datePrevision)
    {
        $this->datePrevision = $datePrevision;
        return $this;
    }

    /**
     * Get datePrevision
     *
     * @return date $datePrevision
     */
    public function getDatePrevision()
    {
        return $this->datePrevision;
    }

    /**
     * {@inheritDoc}
     */
    public function plannifie(){}

    /**
     * {@inheritDoc}
     */
    public function termine(){}

    /**
     * {@inheritDoc}
     */
    public function annule(){}

    /**
     * Remove technicien
     *
     * @param AppBundle\Document\Compte $technicien
     */
    public function removeTechnicien(\AppBundle\Document\Compte $technicien)
    {
        $this->techniciens->removeElement($technicien);
    }

    /**
     * Set emailTransmission
     *
     * @param string $emailTransmission
     * @return $this
     */
    public function setEmailTransmission($emailTransmission)
    {
        $this->emailTransmission = $emailTransmission;
        return $this;
    }

    /**
     * Get emailTransmission
     *
     * @return string $emailTransmission
     */
    public function getEmailTransmission()
    {
        return $this->emailTransmission;
    }

    /**
     * Set secondEmailTransmission
     *
     * @param string $secondEmailTransmission
     * @return $this
     */
    public function setSecondEmailTransmission($secondEmailTransmission)
    {
        $this->secondEmailTransmission = $secondEmailTransmission;
        return $this;
    }

    /**
     * Get secondEmailTransmission
     *
     * @return string $secondEmailTransmission
     */
    public function getSecondEmailTransmission()
    {
        return $this->secondEmailTransmission;
    }

    /**
     * Set nomTransmission
     *
     * @param string $nomTransmission
     * @return $this
     */
    public function setNomTransmission($nomTransmission)
    {
        $this->nomTransmission = $nomTransmission;
        return $this;
    }

    /**
     * Get nomTransmission
     *
     * @return string $nomTransmission
     */
    public function getNomTransmission()
    {
        return $this->nomTransmission;
    }

    /**
     * Set dateDebut
     *
     * @param date $dateDebut
     * @return $this
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return date $dateDebut
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param date $dateFin
     * @return $this
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * Get dateFin
     *
     * @return date $dateFin
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    public function getTypePlanifiable() {
        return 'Devis';
    }
}

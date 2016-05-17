<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Model\DocumentSocieteInterface;
use AppBundle\Manager\FactureManager;
use AppBundle\Manager\ContratManager;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\FactureRepository")
 */
class Facture implements DocumentSocieteInterface {

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\FactureGenerator"})
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Societe", inversedBy="factures", simple=true)
     */
    protected $societe;

    /**
     * @MongoDB\EmbedOne(targetDocument="FactureSoussigne")
     */
    protected $emetteur;

    /**
     * @MongoDB\EmbedOne(targetDocument="FactureSoussigne")
     */
    protected $destinataire;

    /**
     * @MongoDB\Date
     */
    protected $dateEmission;

    /**
     * @MongoDB\Date
     */
    protected $dateFacturation;

    /**
     * @MongoDB\Date
     */
    protected $datePaiement;

    /**
     * @MongoDB\Date
     */
    protected $dateLimitePaiement;

    /**
     * @MongoDB\Float
     */
    protected $montantHT;

    /**
     * @MongoDB\Float
     */
    protected $montantTTC;

    /**
     * @MongoDB\Float
     */
    protected $montantTaxe;

    /**
     * @MongoDB\EmbedMany(targetDocument="FactureLigne")
     */
    protected $lignes;

    /**
     * @MongoDB\String
     */
    protected $identifiantReprise;

    /**
    * @MongoDB\String
    */
   protected $description;

     /**
     * @MongoDB\String
     */
    protected $numeroFacture;

    /**
    * @MongoDB\String
    */
    protected $avoir;

    public function __construct()
    {
        $this->lignes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->emetteur = new FactureSoussigne();
        $this->destinataire = new FactureSoussigne();
    }

    public function update() {
    	$montant = 0;
    	$montantTaxe = 0;
        foreach($this->getLignes() as $ligne) {
            $ligne->update();
            $montant = $montant + $ligne->getMontantHT();
            $montantTaxe = $montantTaxe + $ligne->getMontantTaxe();
        }
        $this->setMontantHT(round($montant, 2));
        $this->setMontantTTC(round($montant + $montantTaxe, 2));
    }

    public function storeDestinataire() {
        $societe = $this->getSociete();
        $destinataire = $this->getDestinataire();

        $destinataire->setNom($societe->getRaisonSociale());
        $destinataire->setAdresse($societe->getAdresse()->getAdresseFormatee());
        $destinataire->setCodePostal($societe->getAdresse()->getCodePostal());
        $destinataire->setCommune($societe->getAdresse()->getCommune());
        $destinataire->setCodeComptable($societe->getCodeComptable());
    }

    public function facturerMouvements() {
        foreach($this->getLignes() as $ligne) {
            $ligne->facturerMouvement();
        }
    }

    public function isPaye() {

        return false;
    }

    public function getOrigines() {
        $origines = array();
        foreach($this->getLignes() as $ligne) {
            if(!$ligne->getOrigineDocument()) {
                continue;
            }
            $origines[$ligne->getOrigineDocument()->getId()] = $ligne->getOrigineDocument();
        }

        return $origines;
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
     * Set montantHT
     *
     * @param float $montantHT
     * @return self
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
     * @return self
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
     * @return self
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
     * Set dateEmission
     *
     * @param date $dateEmission
     * @return self
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
     * Set dateFacturation
     *
     * @param date $dateFacturation
     * @return self
     */
    public function setDateFacturation($dateFacturation)
    {
        $this->dateFacturation = $dateFacturation;
        return $this;
    }

    /**
     * Get dateFacturation
     *
     * @return date $dateFacturation
     */
    public function getDateFacturation()
    {
        return $this->dateFacturation;
    }

    /**
     * Set datePaiement
     *
     * @param date $datePaiement
     * @return self
     */
    public function setDatePaiement($datePaiement)
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    /**
     * Get datePaiement
     *
     * @return date $datePaiement
     */
    public function getDatePaiement()
    {
        return $this->datePaiement;
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
        $this->storeDestinataire();

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
     * Set emetteur
     *
     * @param AppBundle\Document\FactureSoussigne $emetteur
     * @return self
     */
    public function setEmetteur(\AppBundle\Document\FactureSoussigne $emetteur)
    {
        $this->emetteur = $emetteur;
        return $this;
    }

    /**
     * Get emetteur
     *
     * @return AppBundle\Document\FactureSoussigne $emetteur
     */
    public function getEmetteur()
    {
        return $this->emetteur;
    }

    /**
     * Set destinataire
     *
     * @param AppBundle\Document\FactureSoussigne $destinataire
     * @return self
     */
    public function setDestinataire(\AppBundle\Document\FactureSoussigne $destinataire)
    {
        $this->destinataire = $destinataire;
        return $this;
    }

    /**
     * Get destinataire
     *
     * @return AppBundle\Document\FactureSoussigne $destinataire
     */
    public function getDestinataire()
    {
        return $this->destinataire;
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
     * Set numeroFacture
     *
     * @param string $numeroFacture
     * @return self
     */
    public function setNumeroFacture($numeroFacture)
    {
        $this->numeroFacture = $numeroFacture;
        return $this;
    }

    /**
     * Get numeroFacture
     *
     * @return string $numeroFacture
     */
    public function getNumeroFacture()
    {
        return $this->numeroFacture;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
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
     * Set avoir
     *
     * @param string $avoir
     * @return self
     */
    public function setAvoir($avoir)
    {
        $this->avoir = $avoir;
        return $this;
    }

    /**
     * Get avoir
     *
     * @return string $avoir
     */
    public function getAvoir()
    {
        return $this->avoir;
    }

    /**
     * Set dateLimitePaiement
     *
     * @param date $dateLimitePaiement
     * @return self
     */
    public function setDateLimitePaiement($dateLimitePaiement)
    {
        $this->dateLimitePaiement = $dateLimitePaiement;
        return $this;
    }

    /**
     * Get dateLimitePaiement
     *
     * @return date $dateLimitePaiement
     */
    public function getDateLimitePaiement()
    {
        return $this->dateLimitePaiement;
    }
    
    public function getTva() {
    	$tva = 0;
    	foreach ($this->getLignes() as $ligne) {
    		if (!$tva) {
    			$tva = $ligne->getTauxTaxe();
    		}
    		if ($tva != $ligne->getTauxTaxe()) {
    			throw new \Exception("TVA différente dans les lignes de facture.");
    		}
    	}
    	return $tva;
    }
    
    public function getDateReglement() {
    	$frequence = null;
    	foreach ($this->getLignes() as $ligne) {
    		if ($ligne->isOrigineContrat()) {
	    		if (!$frequence) {
	    			$frequence = $ligne->getOrigineDocument()->getFrequencePaiement();
	    		}
	    		if ($frequence != $ligne->getOrigineDocument()->getFrequencePaiement()) {
	    			throw new \Exception("Fréquence de paiement différente dans les lignes de facture.");
	    		}
    		}
    	}
    	$date = $this->getDateFacturation();
    	$date = ($date)? $date : $this->getDateEmission();
    	$date = ($date)? $date : new \DateTime();
    	switch ($frequence) {
    		case ContratManager::FREQUENCE_30J : 
    			$date->modify('+30 day');
    			break;
    		case ContratManager::FREQUENCE_30JMOIS : 
    			$date->modify('+30 day')->modify('last day of');
    			break;
    		case ContratManager::FREQUENCE_45JMOIS : 
    			$date->modify('+45 day')->modify('last day of');
    			break;
    		case ContratManager::FREQUENCE_60J : 
    			$date->modify('+60 day');
    			break;
    		default:
    			$date->modify('+'.FactureManager::DEFAUT_FREQUENCE_JOURS.' day');
    	}
    	return $date;
    }
}

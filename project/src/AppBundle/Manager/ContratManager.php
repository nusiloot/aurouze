<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Model\MouvementManagerInterface;
use AppBundle\Document\Contrat;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Passage;
use AppBundle\Document\CompteInfos;
use AppBundle\Document\Prestation;
use AppBundle\Document\Produit;
use AppBundle\Document\Societe;
use AppBundle\Model\DocumentFacturableInterface;

class ContratManager implements MouvementManagerInterface {

    const STATUT_BROUILLON = "BROUILLON";
    const STATUT_EN_ATTENTE_ACCEPTATION = "EN_ATTENTE_ACCEPTATION";
    const STATUT_EN_COURS = "EN_COURS";
    const STATUT_A_VENIR = "A_VENIR";
    const STATUT_FINI = "FINI";
    const STATUT_RESILIE = "RESILIE"; // statut à retirer = ce n'est pas un statut mais un type!!
    const STATUT_ANNULE = "ANNULE";

    const TYPE_CONTRAT_RECONDUCTION_TACITE = 'RECONDUCTION_TACITE';
    const TYPE_CONTRAT_PONCTUEL = 'PONCTUEL';
    const TYPE_CONTRAT_RENOUVELABLE_SUR_PROPOSITION = 'RENOUVELABLE_SUR_PROPOSITION';
    const TYPE_CONTRAT_AUTRE = 'AUTRE';
    const TYPE_CONTRAT_ANNULE = 'ANNULE';
    const MOYEN_3D = 'MOYEN_3D';
    const MOYEN_PIGEONS = 'MOYEN_PIGEONS';
    const MOYEN_BOIS = 'MOYEN_BOIS';
    const MOYEN_VO = 'MOYEN_VO';

    const FREQUENCE_RECEPTION = 'RECEPTION';
    const FREQUENCE_30J = '30J';
    const FREQUENCE_30JMOIS = '30JMOIS';
    const FREQUENCE_45JMOIS = '45JMOIS';
    const FREQUENCE_60J = '60J';

    public static $moyens_contrat_libelles = array(
        self::MOYEN_3D => '3D',
        self::MOYEN_PIGEONS => 'Pigeons',
        self::MOYEN_BOIS => 'Bois',
        self::MOYEN_VO => 'V.O'
    );
    public static $types_contrat_libelles = array(self::TYPE_CONTRAT_RECONDUCTION_TACITE => 'Reconduction tacite',
        self::TYPE_CONTRAT_PONCTUEL => 'Ponctuel',
        self::TYPE_CONTRAT_RENOUVELABLE_SUR_PROPOSITION => 'Renouvelable sur proposition',
        self::TYPE_CONTRAT_AUTRE => 'Autre',
        self::TYPE_CONTRAT_ANNULE => 'Résilié'
    );
    public static $types_contrat_import_index = array(1 => self::TYPE_CONTRAT_RECONDUCTION_TACITE,
        2 => self::TYPE_CONTRAT_PONCTUEL,
        3 => self::TYPE_CONTRAT_RENOUVELABLE_SUR_PROPOSITION,
        4 => self::TYPE_CONTRAT_AUTRE
    );
    public static $statuts_libelles = array(
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_EN_ATTENTE_ACCEPTATION => "En attente",
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_A_VENIR => 'A venir',
        self::STATUT_FINI => 'Terminé',
        self::STATUT_RESILIE => 'Résilié',
        self::STATUT_ANNULE => 'Annulé'
    );

    public static $statuts_libelles_long = array(
        self::STATUT_BROUILLON => 'en brouillon',
        self::STATUT_EN_ATTENTE_ACCEPTATION => "en attente d'acceptation",
        self::STATUT_EN_COURS => 'en cours',
        self::STATUT_A_VENIR => 'à venir',
        self::STATUT_FINI => 'terminé',
        self::STATUT_RESILIE => 'résilié'
    );
    public static $statuts_couleurs = array(
        self::STATUT_BROUILLON => 'info',
        self::STATUT_EN_ATTENTE_ACCEPTATION => "warning",
        self::STATUT_EN_COURS => 'default',
        self::STATUT_A_VENIR => 'default',
        self::STATUT_FINI => 'success',
        self::STATUT_RESILIE => 'danger',
        self::STATUT_ANNULE => 'danger'
    );
    public static $statuts_positions = array(
        self::STATUT_BROUILLON => 0,
        self::STATUT_EN_ATTENTE_ACCEPTATION => 2,
        self::STATUT_EN_COURS => 1,
        self::STATUT_A_VENIR => 3,
        self::STATUT_FINI => 4,
        self::STATUT_RESILIE => 5,
        self::STATUT_ANNULE => 6
    );
    public static $frequences = array(
    	self::FREQUENCE_RECEPTION => 'A reception',
    	self::FREQUENCE_30J => '30 jours',
    	self::FREQUENCE_30JMOIS => '30 jours fin de mois',
    	self::FREQUENCE_45JMOIS => '45 jours fin de mois',
    	self::FREQUENCE_60J => '60 jours'
    );
    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function createBySociete(Societe $societe, \DateTime $dateCreation = null, Etablissement $etablissement = null) {
        if (!$dateCreation) {
            $dateCreation = new \DateTime();
        }
        $contrat = new Contrat();
        $contrat->setSociete($societe);
        $contrat->setDateCreation($dateCreation);
        $contrat->setStatut(self::STATUT_BROUILLON);
        $contrat->addPrestation(new Prestation());
        $contrat->addProduit(new Produit());

        if ($etablissement) {
            $contrat->addEtablissement($etablissement);
        } else {
            $contrat->addEtablissement($societe->getEtablissements()->first());
        }

        return $contrat;
    }

    function create(Etablissement $etablissement, \DateTime $dateCreation = null) {
        $contrat = $this->createBySociete($etablissement->getSociete());
        $contrat->addEtablissement($etablissement);
        return $contrat;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Contrat');
    }

    private function removeAllPassagesForContrat($contrat) {
        foreach ($contrat->getContratPassages() as $contratPassage) {
            foreach ($contratPassage->getPassages() as $p) {
                $this->dm->remove($p);
            }
        }
        $contrat->reInitContratPassages();
    }

    public function generateAllPassagesForContrat($contrat) {
        $this->removeAllPassagesForContrat($contrat);

        $date_debut = $contrat->getDateDebut();
        $date_acceptation = $contrat->getDateAcceptation();
        if (!$date_debut && !$date_acceptation) {
            return false;
        }
        $date_debut = clone $contrat->getDateDebut();
        $passagesArray = $contrat->getPrevisionnel($date_debut);
        ksort($passagesArray);
        foreach ($contrat->getEtablissements() as $etablissement) {
            $cpt = 0;
            foreach ($passagesArray as $datePassage => $passageInfos) {
                $datePrevision = new \DateTime($datePassage);
                $passage = new Passage();
                $passage->setEtablissement($etablissement);
                $passage->setEtablissementIdentifiant($etablissement->getIdentifiant());
                $passage->addTechnicien($contrat->getTechnicien());

                $passage->setDatePrevision($datePrevision);
                if (!$cpt) {
                    $passage->setDateDebut($datePrevision);
                }

                $passage->setMouvementDeclenchable($passageInfos->mouvement_declenchable);


                $passage->setContrat($contrat);
                $passage->setTypePassage(PassageManager::TYPE_PASSAGE_CONTRAT);
                foreach ($passageInfos->prestations as $prestationPrevu) {
                    $prestationObj = new Prestation();
                    $prestationObj->setNom($prestationPrevu->getNom());
                    $prestationObj->setNomCourt($prestationPrevu->getNomCourt());
                    $prestationObj->setIdentifiant($prestationPrevu->getIdentifiant());
                    $prestationObj->setNbPassages(0);
                    $passage->addPrestation($prestationObj);
                }
                foreach ($contrat->getProduits() as $produit) {
                    $produitNode = clone $produit;
                    $passage->addProduit($produitNode);
                }

                if ($passage) {
                    $contrat->addPassage($etablissement, $passage);
                    $this->dm->persist($passage);
                }
                $cpt++;
            }
        }
        $this->dm->flush();
    }

    public function getMouvementsBySociete(Societe $societe, $isFaturable, $isFacture) {
        $contrats = $this->getRepository()->findContratMouvements($societe, $isFaturable, $isFacture);
        $mouvements = array();

        foreach ($contrats as $contrat) {
            foreach ($contrat->getMouvements() as $mouvement) {
                if($mouvement->getFacturable() != $isFaturable || $mouvement->getFacture() !=  $isFacture) {
                    continue;
                }
                
                $mouvements[] = $mouvement;
            }
        }

        return $mouvements;
    }

    public function getMouvements($isFaturable, $isFacture) {
        $mouvements = array();

        return $mouvements;
    }

    public function getNbContratWithCompteForCommercial($compte) {
        return $this->getRepository()->countContratByCommercial($compte);
    }

}

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
    const STATUT_VALIDE = "VALIDE";
    const STATUT_FINI = "FINI";
    const STATUT_RESILIE = "RESILIE";
    const TYPE_CONTRAT_RECONDUCTION_TACITE = 'RECONDUCTION_TACITE';
    const TYPE_CONTRAT_PONCTUEL = 'PONCTUEL';
    const TYPE_CONTRAT_RENOUVELABLE_SUR_PROPOSITION = 'RENOUVELABLE_SUR_PROPOSITION';
    const TYPE_CONTRAT_AUTRE = 'AUTRE';

    public static $types_contrat_libelles = array(self::TYPE_CONTRAT_RECONDUCTION_TACITE => 'Reconduction tacite',
        self::TYPE_CONTRAT_PONCTUEL => 'Ponctuel',
        self::TYPE_CONTRAT_RENOUVELABLE_SUR_PROPOSITION => 'Renouvelable sur proposition',
        self::TYPE_CONTRAT_AUTRE => 'Autre'
    );
    public static $types_contrat_import_index = array(1 => self::TYPE_CONTRAT_RECONDUCTION_TACITE,
        2 => self::TYPE_CONTRAT_PONCTUEL,
        3 => self::TYPE_CONTRAT_RENOUVELABLE_SUR_PROPOSITION,
        4 => self::TYPE_CONTRAT_AUTRE
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

    public function generateAllPassagesForContrat($contrat) {
        if (count($contrat->getContratPassages())) {
            return;
        }
        $date_debut = $contrat->getDateDebut();
        if (!$date_debut) {
            return false;
        }
        $date_debut = clone $contrat->getDateDebut();
        $passagesArray = $contrat->getPrevisionnel($date_debut);
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

                $passage->generateId();
                $passage->setContrat($contrat);
                foreach ($passageInfos->prestations as $prestationNom) {
                    $prestationObj = new Prestation();
                    $prestationObj->setNom($prestationNom);
                    $passage->addPrestation($prestationObj);
                }
                foreach ($contrat->getProduits() as $produit) {
                    $produitNode = clone $produit;
                    $passage->addProduit($produitNode);
                }

                if ($passage) {
                    $contrat->addPassage($etablissement, $passage);
                    $this->dm->persist($passage);
                    $this->dm->persist($contrat);
                }
                $cpt++;
                $this->dm->flush();
            }
        }
    }

    public function getMouvementsBySociete(Societe $societe, $isFaturable, $isFacture) {
        $contrats = $this->getRepository()->findContratMouvements($societe, $isFaturable, $isFacture);
        $mouvements = array();

        foreach ($contrats as $contrat) {
            foreach ($contrat->getMouvements() as $mouvement) {
                $mouvement->setOrigineDocument($contrat);
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

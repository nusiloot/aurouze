<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Document\Passage;

class PassageManager {

    const STATUT_EN_ATTENTE = "EN_ATTENTE";
    const STATUT_A_PLANIFIER = "A_PLANIFIER";
    const STATUT_PLANIFIE = "PLANIFIE";
    const STATUT_REALISE = "REALISE";
    const STATUT_ANNULE = "ANNULE";
    const TYPE_PASSAGE_CONTRAT = "CONTRAT";
    const TYPE_PASSAGE_GARANTIE = "GARANTIE";
    const TYPE_PASSAGE_CONTROLE = "CONTROLE";

    public static $statutsLibellesActions = array(self::STATUT_A_PLANIFIER => 'A planifier',
        self::STATUT_EN_ATTENTE => 'Prévu',
        self::STATUT_PLANIFIE => 'Planifié',
        self::STATUT_REALISE => 'Réalisé', self::STATUT_ANNULE => 'Annulé');
    public static $statutsLibelles = array(self::STATUT_A_PLANIFIER => 'À planifier',
        self::STATUT_EN_ATTENTE => 'En attente',
        self::STATUT_PLANIFIE => 'Planifié',
        self::STATUT_REALISE => 'Réalisé', self::STATUT_ANNULE => 'Annulé');
    public static $typesPassageLibelles = array(
        self::TYPE_PASSAGE_CONTRAT => "Sous contrat",
        self::TYPE_PASSAGE_GARANTIE => "Sous garantie",
        self::TYPE_PASSAGE_CONTROLE => "Contrôle",
    );
    
    public static $applications = array(
    	'En place',
    	'Souillés',
    	'Disparus',
    	'Ecrasés',
    	'Déplacés'
    );


    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function create(Etablissement $etablissement, Contrat $contrat) {
        $passage = new Passage();

        $passage->setEtablissement($etablissement);
        $passage->setContrat($contrat);

        return $passage;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Passage');
    }

    public function getNextPassageFromPassage($passage) {
        $contrat = $passage->getContrat();

        $etablissement = $passage->getEtablissement();
        $passagesEtablissement = $contrat->getPassagesEtablissementNode($etablissement);
        $nextPassage = null;
        $founded = false;
        foreach ($passagesEtablissement->getPassagesSorted() as $key => $passageEtb) {
            $nextPassage = $passageEtb;
            if ($founded) {
                break;
            }
            if ($key == $passage->getId()) {
                $founded = true;
            }
        }
        return $nextPassage;
    }

    public function isFirstPassageNonRealise($passage) {
        $contrat = $passage->getContrat();

        $etablissement = $passage->getEtablissement();
        $passagesEtablissement = $contrat->getPassagesEtablissementNode($etablissement);
        $passagePrecedent = null;
        foreach ($passagesEtablissement->getPassagesSorted() as $key => $passageEtb) {
            if (($passage->getId() == $passageEtb->getId()) && (is_null($passagePrecedent) || $passagePrecedent->isRealise())) {
                return true;
            }
            $passagePrecedent = $passageEtb;
        }
        return false;
    }

    public function getNbPassagesWithTechnicien($compte) {

        return $this->getRepository()->countPassagesByTechnicien($compte);
    }

    public function getPassagesByNumeroArchiveContrat(Passage $passage) {
        $contratsByNumero = $this->dm->getRepository('AppBundle:Contrat')->findByNumeroArchive($passage->getContrat()->getNumeroArchive());
        $passagesByNumero = array();
        foreach ($contratsByNumero as $contrat) {
            foreach ($contrat->getContratPassages() as $contratPassages) {
                $idEtb = $contratPassages->getEtablissement()->getId();
                if (!array_key_exists($idEtb, $passagesByNumero)) {
                    $passagesByNumero[$idEtb] = array();
                }
                foreach ($contratPassages->getPassages() as $passage) {
                    $passagesByNumero[$idEtb][$passage->getDatePrevision()->format('Y-m-d')] = $passage;
                }
                ksort($passagesByNumero[$idEtb]);
            }
        }
        return $passagesByNumero;
    }

    public function passagePrecedent(Passage $passage) {
        $lastPassage = null;
        $passagesArrayByNumeroArchive = $this->getPassagesByNumeroArchiveContrat($passage);
        foreach ($passagesArrayByNumeroArchive as $etbId => $passagesEtb) {
            foreach ($passagesEtb as $p) {
                if($p->getId() == $passage->getId()){
                    return $lastPassage;
                }
                $lastPassage = $p;
            }
        }
        return null;
    }

}

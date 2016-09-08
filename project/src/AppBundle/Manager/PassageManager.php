<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Document\Passage;
use AppBundle\Manager\ContratManager;

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
    protected $cm;

    function __construct(DocumentManager $dm, ContratManager $cm) {
        $this->dm = $dm;
        $this->cm = $cm;
    }

    function create(Etablissement $etablissement, Contrat $contrat) {
        $passage = new Passage();

        $passage->setEtablissement($etablissement);

        foreach ($contrat->getPrestations() as $prestationContrat) {
            $prestation = clone $prestationContrat;
            $prestation->setNbPassages(0);
            $passage->addPrestation($prestation);
        }
        $previousPassage = null;
        foreach ($contrat->getPassagesEtablissementNode($etablissement)->getPassagesSorted(true) as $p) {
            if (($p->getId() != $passage->getId()) && count($p->getTechniciens())) {
                $previousPassage = $p;
                break;
            }
        }
        if ($previousPassage) {
            foreach ($previousPassage->getTechniciens() as $tech) {
                $passage->addTechnicien($tech);
            }
        } elseif ($contrat->getTechnicien()) {
            $passage->addTechnicien($contrat->getTechnicien());
        }
        foreach ($contrat->getProduits() as $produitContrat) {
            $produit = clone $produitContrat;
            $produit->setNbUtilisePassage(0);
            $passage->addProduit($produit);
        }
        $passage->setContrat($contrat);
        return $passage;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Passage');
    }

    public function getNextPassageFromPassageInList($passage, $passagesList, $return_self = true) {

        $nextPassage = null;
        $founded = false;
        foreach ($passagesList as $key => $passageEtb) {
            $nextPassage = $passageEtb;
            if ($founded) {
                if (!$return_self) {
                    return $nextPassage;
                } else {
                    break;
                }
            }

            if ($passageEtb->getId() == $passage->getId() && $passage->isSousContrat()) {
                $founded = true;
            }
        }
        if (!$return_self) {
            return null;
        } else {
            return $nextPassage;
        }
    }

    public function getNextPassageFromPassage($passage, $withInterContrat = false) {
        if (!$withInterContrat) {
            $contrat = $passage->getContrat();
            $passagesEtablissement = $contrat->getPassagesEtablissementNode($passage->getEtablissement());
            return $this->getNextPassageFromPassageInList($passage, $passagesEtablissement->getPassagesSorted());
        } else {
            $passagesByNumeroArchiveContrat = $this->getPassagesByNumeroArchiveContrat($passage);
            $passagesByNumeroArchiveEtablissement = $passagesByNumeroArchiveContrat[$passage->getEtablissement()->getId()];
            return $this->getNextPassageFromPassageInList($passage, $passagesByNumeroArchiveEtablissement,false);
        }
        return null;
    }

    public function updateNextPassageAPlannifier($passage) {
        $nextPassage = $this->getNextPassageFromPassage($passage, true);
        while ($nextPassage && !$nextPassage->isEnAttente()) {
            $nextPassage = $this->getNextPassageFromPassage($nextPassage, true);
        }
        if ($nextPassage) {
            $nextPassage->setDateDebut($nextPassage->getDatePrevision());
            $nextPassage->setDureePrecedente($passage->getDureeDate());
            $nextPassage->setDatePrecedente($passage->getDateDebut());
            $nextPassage->copyTechnicienFromPassage($passage);
        }
        return $nextPassage;
    }

    public function updateNextPassageEnAttente($passage) {
        $nextPassage = $this->getNextPassageFromPassage($passage);
        if ($nextPassage && $nextPassage->isAPlanifie()) {
            $nextPassage->setDateDebut(null);
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

    public function getPassagesByNumeroArchiveContrat(Passage $passage, $reverse = false) {
       return $this->cm->getPassagesByNumeroArchiveContrat($passage->getContrat(),$reverse);
    }

    public function passagePrecedentRealiseSousContrat(Passage $passage) {
        $lastPassage = null;
        $passagesArrayByNumeroArchive = $this->getPassagesByNumeroArchiveContrat($passage);
        foreach ($passagesArrayByNumeroArchive as $etbId => $passagesEtb) {
            foreach ($passagesEtb as $p) {
                if ($p->getId() == $passage->getId()) {
                    return $lastPassage;
                }
                if($p->isSousContrat() && $p->isRealise()) {
                    $lastPassage = $p;
                }
            }
        }
        return null;
    }

    public function passagePrecedentSousContrat(Passage $passage) {
        $lastPassage = null;
        $passagesArrayByNumeroArchive = $this->getPassagesByNumeroArchiveContrat($passage);
        foreach ($passagesArrayByNumeroArchive as $etbId => $passagesEtb) {
            foreach ($passagesEtb as $p) {
                if ($p->getId() == $passage->getId()) {
                    return $lastPassage;
                }
                if($p->isSousContrat()) {
                    $lastPassage = $p;
                }
            }
        }
        return null;
    }

    public function getNbPassagesToPlanPerMonth($passages) {
        $result = array();
        foreach ($passages as $passage) {
            $moisAnnee = $passage->getDatePrevision()->format('Ym');
            if (!array_key_exists($moisAnnee, $result)) {
                $result[$moisAnnee] = new \stdClass();
                $result[$moisAnnee]->nb = 0;
                $result[$moisAnnee]->date = $passage->getDatePrevision();
            }
            $result[$moisAnnee]->nb = $result[$moisAnnee]->nb + 1;
        }
        return $result;
    }

}

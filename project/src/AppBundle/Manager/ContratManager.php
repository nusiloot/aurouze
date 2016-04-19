<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Model\MouvementManagerInterface;
use AppBundle\Document\Contrat;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Passage;
use AppBundle\Document\UserInfos;
use AppBundle\Document\Prestation;
use AppBundle\Document\Produit;
use AppBundle\Document\Societe;
use AppBundle\Model\DocumentFacturableInterface;

class ContratManager implements MouvementManagerInterface {

    const STATUT_BROUILLON = "BROUILLON";
    const STATUT_EN_ATTENTE_ACCEPTATION = "EN_ATTENTE_ACCEPTATION";
    const STATUT_VALIDE = "VALIDE";

    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function createBySocieteWithFirstEtablissement(Societe $societe, \DateTime $dateCreation = null) {
        $contrat = $this->createBySociete($societe);
        if (count($societe->getEtablissements())) {
            $etablissement = null;
            foreach ($societe->getEtablissements() as $etb) {
                $etablissement = $etb;
                break;
            }

            $contrat->addEtablissement($etablissement);
        }
        return $contrat;
    }

    function createBySociete(Societe $societe, \DateTime $dateCreation = null) {
        if (!$dateCreation) {
            $dateCreation = new \DateTime();
        }
        $contrat = new Contrat();
        $contrat->setSociete($societe);
        $contrat->setDateCreation($dateCreation);
        $contrat->setStatut(self::STATUT_BROUILLON);
        $contrat->addPrestation(new Prestation());
        $contrat->addProduit(new Produit());
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

    public function getNextPassageForContrat($contrat) {
        $nextPassage = $contrat->getNextPassage();
        if ($nextPassage) {
            $userInfos = new UserInfos();
            $user = $this->dm->getRepository('AppBundle:User')->findOneById($contrat->getTechnicien()->getId());
            if ($user) {
                $userInfos->copyFromUser($user);
            }
            $nextPassage->setTechnicienInfos($userInfos);
        }
        return $nextPassage;
    }

    public function generateAllPassagesForContrat($contrat) {
        if(count($contrat->getContratPassages())){
            throw new \Exception("Les passages de ce contrat ont déjà été généré");
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
                $passage->setNumeroPassageIdentifiant("001");
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

}

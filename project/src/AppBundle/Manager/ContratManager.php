<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Model\MouvementManagerInterface;
use AppBundle\Document\Contrat;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Passage;
use AppBundle\Document\UserInfos;
use AppBundle\Document\Prestation;

class ContratManager implements MouvementManagerInterface {

    const STATUT_BROUILLON = "BROUILLON";
    const STATUT_EN_ATTENTE_ACCEPTATION = "EN_ATTENTE_ACCEPTATION";
    const STATUT_VALIDE = "VALIDE";

    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function create(Etablissement $etablissement, \DateTime $dateCreation = null) {
        if (!$dateCreation) {
            $dateCreation = new \DateTime();
        }
        $contrat = new Contrat();
        $contrat->setEtablissement($etablissement);
        $contrat->setDateCreation($dateCreation);
        $contrat->setIdentifiant($this->getNextNumero($etablissement, $dateCreation));
        $contrat->generateId();
        $contrat->setStatut(self::STATUT_BROUILLON);
        $contrat->addPrestation(new Prestation());
        return $contrat;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Contrat');
    }

    public function getNextNumero(Etablissement $etablissement, \DateTime $dateCreation) {
        $next = $this->getRepository()->findNextNumero($etablissement, $dateCreation);
        return $etablissement->getIdentifiant() . '-' . $dateCreation->format('Ymd') . '-' . sprintf("%03d", $next);
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
        $date_debut = $contrat->getDateDebut();
        if (!$date_debut) {
            return false;
        }

        $passagesArray = $contrat->getPrevisionnel($date_debut);
        $cpt = 0;
        foreach ($passagesArray as $datePassage => $passageInfos) {
            $datePrevision = new \DateTime($datePassage);
            $passage = new Passage();
            $passage->setEtablissementIdentifiant($contrat->getEtablissement()->getIdentifiant());

            $passage->setDatePrevision($datePrevision);
            if(!$cpt){
                $passage->setDateDebut($datePrevision);
            }
            $passage->getEtablissementInfos()->pull($contrat->getEtablissement());
            $passage->setNumeroPassageIdentifiant("001");
            $passage->setMouvementDeclenchable($passageInfos->mouvement_declenchable);

            $passage->generateId();
            $passage->setContrat($contrat);
            foreach ($passageInfos->prestations as $prestationNom) {
                $prestationObj = new Prestation();
                $prestationObj->setNom($prestationNom);
                $passage->addPrestation($prestationObj);
            }

            if ($passage) {
                $contrat->addPassage($passage);
                $this->dm->persist($passage);
                $this->dm->persist($contrat);
            }
            $cpt++;
            $this->dm->flush();
        }
    }

    public function getMouvementsByEtablissement(Etablissement $etablissement, $isFaturable, $isFacture) {
        $contrats = $this->getRepository()->findContratMouvements($etablissement, $isFaturable,  $isFacture);
        $mouvements = array();
        foreach($contrats as $contrat) {
            $mouvements = array_merge($mouvements, $contrat->getMouvements()->toArray());
        }

        return $mouvements;
    }

    public function getMouvements($isFaturable, $isFacture) {
        $mouvements = array();

        return $mouvements;
    }

}

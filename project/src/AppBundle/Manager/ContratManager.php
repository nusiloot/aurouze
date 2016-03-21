<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Contrat;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Passage;
use AppBundle\Document\UserInfos;

class ContratManager {


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

   
}

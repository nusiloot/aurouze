<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Etablissement as Etablissement;
use AppBundle\Document\Passage as Passage;

class PassageManager {

    const STATUT_EN_ATTENTE = "EN_ATTENTE";
    const STATUT_A_PLANIFIER = "A_PLANIFIER";
    const STATUT_PLANIFIE = "PLANIFIE";
    const STATUT_REALISE = "REALISE";

    public static $statutsLibellesActions = array(self::STATUT_A_PLANIFIER => 'A planifier',
        self::STATUT_EN_ATTENTE => 'Prévu',
        self::STATUT_PLANIFIE => 'Planifié',
        self::STATUT_REALISE => 'Réalisé');
    public static $statutsLibelles = array(self::STATUT_A_PLANIFIER => 'À planifier',
        self::STATUT_EN_ATTENTE => 'En attente',
        self::STATUT_PLANIFIE => 'Planifié',
        self::STATUT_REALISE => 'Réalisé');
    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function create(Etablissement $etablissement) {
        $passage = new Passage();

        $passage->setEtablissement($etablissement);

        $numeroPassageIdentifiant = $this->getNextNumeroPassage($etablissement->getIdentifiant(), new \DateTime());
        $passage->setNumeroPassageIdentifiant($numeroPassageIdentifiant);
        $passage->generateId();
        return $passage;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Passage');
    }

    public function getNextNumeroPassage($etablissementIdentifiant, \DateTime $date) {
        $allPassagesForEtablissementsInDay = $this->getRepository()->findPassagesForEtablissementsAndDay($etablissementIdentifiant, $date);

        if (!count($allPassagesForEtablissementsInDay)) {
            return sprintf("%03d", 1);
        }
        return sprintf("%03d", max($allPassagesForEtablissementsInDay) + 1);
    }
    
     public function getNextPassageFromPassage($passage) {
        $contrat = $passage->getContrat();
        $etablissement = $passage->getEtablissement();
        $passagesEtablissement = $contrat->getPassagesEtablissementNode($etablissement);
        $nextPassage = null;
        $founded = false;
        foreach($passagesEtablissement->getPassagesSorted() as $key => $passageEtb){
            $nextPassage = $passageEtb;
            if($founded){
                break;
            }
            if($key == $passage->getId()){
                $founded = true;
            }           
        }
        return $nextPassage;
    }

}

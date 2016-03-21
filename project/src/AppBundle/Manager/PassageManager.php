<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Etablissement as Etablissement;
use AppBundle\Document\Passage as Passage;

class PassageManager {
const STATUT_NON_PLANIFIE = "NON_PLANIFIE";
const STATUT_PLANIFIE = "PLANIFIE";
const STATUT_REALISE = "REALISE";

    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function create(Etablissement $etablissement) {
        $passage = new Passage();

        $passage->setEtablissementIdentifiant($etablissement->getIdentifiant());

        $numeroPassageIdentifiant = $this->getNextNumeroPassage($etablissement->getIdentifiant());
        $passage->setNumeroPassageIdentifiant($numeroPassageIdentifiant);
        $passage->setId();
        $passage->updateEtablissementInfos($etablissement);
        return $passage;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Passage');
    }

    public function getNextNumeroPassage($etablissementIdentifiant, \DateTime $date) {
       $allPassagesForEtablissementsInDay = $this->getRepository()->findPassagesForEtablissementsAndDay($etablissementIdentifiant, $date);

        if(!count($allPassagesForEtablissementsInDay)){
            return sprintf("%03d", 1);
        }
        return sprintf("%03d", max($allPassagesForEtablissementsInDay) + 1);
    }

}

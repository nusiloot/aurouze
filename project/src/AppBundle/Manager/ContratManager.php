<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Contrat;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Passage;

class ContratManager {

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
        $contrat->setIdentifiant($this->getNextNumero($etablissement,$dateCreation));
        $contrat->generateId();
        return $contrat;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Contrat');
    }

    public function getNextNumero(Etablissement $etablissement, \DateTime $dateCreation) {
        $next = $this->getRepository()->findNextNumero($etablissement, $dateCreation);
        return $etablissement->getIdentifiant() . '-' . $dateCreation->format('Ymd') . '-' . sprintf("%03d", $next);
    }
    /*
    public function updatePassages(Contrat $c,$old_id = "") {
        $contrat_id = $c->getId();
        if($old_id){
            $contrat_id = $old_id;
        }
        $passages = $this->dm->getRepository('AppBundle:Passage')->findByContratId($contrat_id);
        
        foreach ($passages as $passage) {
            $c->addPassage($passage);
            $passage->setContratId($c->getId());
            $this->dm->persist($passage);
        }    
        $this->dm->flush();
    }*/

}

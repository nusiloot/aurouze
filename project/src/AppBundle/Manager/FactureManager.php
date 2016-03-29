<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Facture;
use AppBundle\Document\Etablissement;
use AppBundle\Manager\MouvementManager;

class FactureManager {

    protected $dm;
    protected $mm;

    function __construct(DocumentManager $dm, MouvementManager $mm) {
        $this->dm = $dm;
        $this->mm = $mm;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Facture');
    }

    public function getMouvementsByEtablissement(Etablissement $etablissement) {

        return $this->mm->getMouvementsByEtablissement($etablissement, true, false);
    }

    public function getMouvements() {

        return $this->mm->getMouvements(true, false);
    }
}

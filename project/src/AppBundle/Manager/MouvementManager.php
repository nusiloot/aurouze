<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Model\MouvementManagerInterface;
use AppBundle\Document\Facture;
use AppBundle\Document\Etablissement;

class MouvementManager implements MouvementManagerInterface {

    protected $mms;

    function __construct(array $mms) {
        $this->mms = $mms;
    }

    public function getMouvementsByEtablissement(Etablissement $etablissement, $isFaturable, $isFacture) {
        $mouvements = array();
        foreach($this->mms as $mm) {
            $mouvements = array_merge($mouvements, $mm->getMouvementsByEtablissement($etablissement, $isFaturable, $isFacture));
        }

        return $mouvements;
    }

    public function getMouvements($isFaturable, $isFacture) {
        $mouvements = array();
        foreach($this->mms as $mm) {
            $mouvements = array_merge($mouvements, $mm->getMouvements($isFaturable, $isFacture));
        }

        return $mouvements;
    }
}

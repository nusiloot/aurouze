<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;

class InterlocuteurManager {

    protected $dm;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function findAll($societe, $withActif = true) {
        $etablissements = $this->dm->getRepository('AppBundle:Etablissement')->findBy(array('societe' => $societe->getId(), 'actif' => true));
        $comptes = $this->dm->getRepository('AppBundle:Compte')->findBy(array('societe' => $societe->getId(), 'actif' => true));

        return array_merge(array($societe), $etablissements, $comptes);
    }

    public function find($id) {
        $societe = $this->dm->getRepository('AppBundle:Societe')->find($id);

        if($societe) {

            return $societe;
        }

        $etablissement = $this->dm->getRepository('AppBundle:Etablissement')->find($id);

        if($etablissement) {

            return $etablissement;
        }

        $compte = $this->dm->getRepository('AppBundle:Compte')->find($id);

        if($compte) {

            return $compte;
        }

        return null;
    }

}

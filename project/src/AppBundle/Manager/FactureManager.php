<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Facture;
use AppBundle\Document\FactureLigne;
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

    public function findByEtablissement(Etablissement $etablissement) {

        return $this->getRepository()->findBy(array('etablissement.id' => $etablissement->getId()));
    }

    public function create(Etablissement $etablissement, $mouvements, $dateFacturation) {
        $facture = new Facture();
        $facture->setEtablissement($etablissement);
        $facture->setDateEmission(new \DateTime());
        $facture->setDateFacturation($dateFacturation);
        $facture->generateId();

        foreach($mouvements as $mouvement) {
            $ligne = new FactureLigne();
            $ligne->setLibelle("");
            $ligne->setQuantite(1);
            $ligne->setPrixUnitaire($mouvement->getPrix());
            $ligne->setTauxTaxe(0.20);
            $facture->addLigne($ligne);
        }

        $facture->update();

        return $facture;
    }

    public function getMouvementsByEtablissement(Etablissement $etablissement) {

        return $this->mm->getMouvementsByEtablissement($etablissement, true, false);
    }

    public function getMouvements() {

        return $this->mm->getMouvements(true, false);
    }
}

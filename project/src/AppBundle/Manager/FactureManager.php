<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Facture;
use AppBundle\Document\FactureLigne;
use AppBundle\Document\Societe;
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

    public function findBySociete(Societe $societe) {

        return $this->getRepository()->findBy(array('societe.id' => $societe->getId()));
    }

    public function create(Societe $societe, $mouvements, $dateFacturation) {
        $facture = new Facture();
        $facture->setSociete($societe);
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

    public function getMouvementsBySociete(Societe $societe) {

        return $this->mm->getMouvementsBySociete($societe, true, false);
    }

    public function getMouvements() {

        return $this->mm->getMouvements(true, false);
    }
}

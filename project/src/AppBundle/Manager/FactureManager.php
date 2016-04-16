<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Facture;
use AppBundle\Document\FactureLigne;
use AppBundle\Document\Societe;
use AppBundle\Document\Adresse;
use AppBundle\Manager\MouvementManager;

class FactureManager {

    protected $dm;
    protected $mm;
    protected $parameters;

    function __construct(DocumentManager $dm, MouvementManager $mm, $parameters) {
        $this->dm = $dm;
        $this->mm = $mm;
        $this->parameters = $parameters;
    }

    public function getParameters() {

        return $this->parameters;
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

        $facture->getEmetteur()->setNom($this->parameters['emetteur']['nom']);
        $adresse = new Adresse();
        $adresse->setAdresse($this->parameters['emetteur']['adresse']);
        $adresse->setCodePostal($this->parameters['emetteur']['code_postal']);
        $adresse->setCommune($this->parameters['emetteur']['commune']);
        $facture->getEmetteur()->setAdresse($adresse);
        $facture->getEmetteur()->setTelephone($this->parameters['emetteur']['telephone']);
        $facture->getEmetteur()->setFax($this->parameters['emetteur']['fax']);

        foreach($mouvements as $mouvement) {
            $ligne = new FactureLigne();
            $ligne->setLibelle($mouvement->getLibelle());
            $ligne->setQuantite(1);
            $ligne->setPrixUnitaire($mouvement->getPrix());
            $ligne->setTauxTaxe(0.20);
            $ligne->setOrigineDocument($mouvement->getOrigineDocument());
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

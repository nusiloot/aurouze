<?php

namespace AppBundle\Model;

use AppBundle\Document\Compte;
use AppBundle\Document\Etablissement;
use AppBundle\Document\RendezVous;

abstract class AbstractDocumentPlannifiable
{
    protected $etablissement;
    protected $technicien;
    protected $datePrevision;
    protected $rendezvous;
    protected $dureePrevisionnelle;

    abstract public function getEtablissement();
    abstract public function setEtablissement(Etablissement $etablissement);
    abstract public function getTechnicien();
    abstract public function setTechnicien(Compte $technicien);
    abstract public function getDatePrevision();
    abstract public function setDatePrevision($datePrevision);
    abstract public function getRendezvous();
    abstract public function setRendezvous(RendezVous $rendezvous);
    abstract public function getDureePrevisionnelle();
    abstract public function setDureePrevisionnelle($dureePrevisionnelle);
}

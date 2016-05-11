<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\Passage;
use AppBundle\Document\RendezVous;

class RendezVousManager {
    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function createOrUpdateFromPassage(Passage $passage) {
        $rdv = $passage->getRendezVous();

        if(!$rdv) {
            $rdv = new RendezVous();
            $rdv->setPassage($passage);
            $this->dm->persist($rdv);
            $passage->setRendezVous($rdv);
        }

        $rdv->setTitre(sprintf("%s (%s %s)",
                $passage->getEtablissementInfos()->getNom(),
                $passage->getEtablissementInfos()->getAdresse()->getCodePostal(), $passage->getEtablissementInfos()->getAdresse()->getCommune()
        ));

        $rdv->setDescription(sprintf("%s\nContrat n°%s\nTraitement : %s",
                $passage->getLibelle(),
                $passage->getContrat()->getNumeroArchive(),
                implode(", ", $passage->getPrestations()->toArray());
        ));

        $rdv->setLieu(sprintf("%s %s %s",
                $passage->getEtablissementInfos()->getAdresse()->getAdresse(),
                $passage->getEtablissementInfos()->getAdresse()->getCodePostal(), $passage->getEtablissementInfos()->getAdresse()->getCommune()
        ));

        $rdv->removeAllParticipants();
        foreach($passage->getTechniciens() as $technicien) {
            $rdv->addParticipant($technicien);
        }
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:RendezVous');
    }

}

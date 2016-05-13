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

    function createFromPassage(Passage $passage, \DateTime $dateDebut, \DateTime $dateFin) {
        $rdv = $passage->getRendezVous();

        if($rdv) {

            throw new \Exception('Le rendez vous est déjà créé');
        }

        $rdv = new RendezVous();
        $rdv->setPassage($passage);

        $rdv->setTitre(sprintf("%s (%s %s)",
                $passage->getEtablissementInfos()->getNom(),
                $passage->getEtablissementInfos()->getAdresse()->getCodePostal(), $passage->getEtablissementInfos()->getAdresse()->getCommune()
        ));

        $rdv->setDescription($passage->getCommentaire());

        $rdv->setLieu(sprintf("%s %s %s",
                $passage->getEtablissementInfos()->getAdresse()->getAdresse(),
                $passage->getEtablissementInfos()->getAdresse()->getCodePostal(), $passage->getEtablissementInfos()->getAdresse()->getCommune()
        ));

        $rdv->removeAllParticipants();
        foreach($passage->getTechniciens() as $technicien) {
            $rdv->addParticipant($technicien);
        }

        $passage->setRendezVous($rdv);

        $rdv->setDateDebut($dateDebut);
        $rdv->setDateFin($dateFin);

        return $rdv;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:RendezVous');
    }

}

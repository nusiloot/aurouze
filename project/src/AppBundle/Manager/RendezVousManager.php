<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Model\DocumentPlannifiableInterface;
use AppBundle\Document\RendezVous;

class RendezVousManager {
    protected $dm;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function createFromPassage(DocumentPlannifiableInterface $passage) {
        $rdv = $passage->getRendezVous();

        if($rdv) {

            throw new \Exception('Le rendez vous est déjà créé');
        }

        $rdv = new RendezVous();
        switch ($passage::DOCUMENT_TYPE) {
            case 'Devis':
                $rdv->setDevis($passage);
                break;
            case 'Passage':
                $rdv->setPassage($passage);
                break;
        }

        $rdv->setTitre(sprintf("%s (%s %s)",
                $passage->getEtablissementInfos()->getNom(),
                $passage->getEtablissementInfos()->getAdresse()->getCodePostal(), $passage->getEtablissementInfos()->getAdresse()->getCommune()
        ));


        $rdv->setLieu(sprintf("%s %s %s",
                $passage->getEtablissementInfos()->getAdresse()->getAdresse(),
                $passage->getEtablissementInfos()->getAdresse()->getCodePostal(), $passage->getEtablissementInfos()->getAdresse()->getCommune()
        ));

        $rdv->removeAllParticipants();
        foreach($passage->getTechniciens() as $technicien) {
            $rdv->addParticipant($technicien);
        }

        $passage->setRendezVous($rdv);

        /* if($passage->getDateDebut() && $passage->getDateFin()) { */
        /*     $rdv->setDateDebut($passage->getDateDebut()); */
        /*     $rdv->setDateFin($passage->getDateFin()); */
        /* } */

        $rdv->setDescription($passage->getEtablissement()->getCommentaire());

        return $rdv;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:RendezVous');
    }

}

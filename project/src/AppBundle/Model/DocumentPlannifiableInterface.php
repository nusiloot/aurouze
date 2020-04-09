<?php

namespace AppBundle\Model;

use AppBundle\Document\Compte;
use AppBundle\Document\Etablissement;
use AppBundle\Document\RendezVous;
use Doctrine\Common\Collections\Collection;

interface DocumentPlannifiableInterface
{
    /**
     * Get etablissement
     *
     * @return Etablissement
     */
    public function getEtablissement();

    /**
     * Set etablissement
     *
     * @param Etablissement
     */
    public function setEtablissement(Etablissement $etablissement);

    /**
     * Get technicien
     *
     * @return Collection
     */
    public function getTechniciens();

    /**
     * Set technicien
     *
     * @param Compte
     */
    public function addTechnicien(Compte $techniciens);

    /**
     * Get DatePrevision
     *
     * @return string
     */
    public function getDatePrevision();

    /**
     * Set DatePrevision
     *
     * @param string
     */
    public function setDatePrevision($datePrevision);

    /**
     * Get RendezVous
     *
     * @return RendezVous
     */
    public function getRendezvous();

    /**
     * Set RendezVous
     *
     * @param RendezVous
     */
    public function setRendezvous(RendezVous $rendezvous);

    /**
     * Get DureePrevisionnelle
     *
     * @return RendezVous
     */
    public function getDureePrevisionnelle();

    /**
     * Set DureePrevisionnelle
     *
     * @param RendezVous
     */
    public function setDureePrevisionnelle($dureePrevisionnelle);

    /**
     * Fonction appellé lorsque l'objet arrive dans le calendrier
     *
     */
    public function plannifie();

    /**
     * Fonction appellé lorsque l'objet a terminé son rendez vous
     *
     */
    public function termine();

    /**
     * Fonction appellé lorsque l'objet a ete annulé
     *
     */
    public function annule();
}

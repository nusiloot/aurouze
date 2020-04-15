<?php

namespace AppBundle\Model;

use AppBundle\Document\Etablissement;
use AppBundle\Document\Compte;
use AppBundle\Document\RendezVous;

trait DocumentPlanifiableMethodsTrait
{
    /**
     * Set etablissement
     *
     * @param Etablissement $etablissement
     * @return $this
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Return Etablissement
     *
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Add technicien
     *
     * @param Compte $technicien
     * @return $this
     */
    public function addTechnicien(Compte $technicien)
    {
        if (! $this->techniciens->contains($technicien)) {
            $this->techniciens[] = $technicien;
        }
        return $this;
    }

    /**
     * Get technicien
     *
     * @return Collection $techniciens
     */
    public function getTechniciens()
    {
        return $this->techniciens;
    }

    public function getTechniciensIds() {
        $techniciens = array();

        foreach ($this->getTechniciens() as $technicien) {
            $techniciens[] = $technicien->getId();
        }

        sort($techniciens);

        return $techniciens;
    }

    /**
     * Remove technicien
     *
     * @param Compte $technicien
     */
    public function removeTechnicien(Compte $technicien)
    {
        $this->techniciens->removeElement($technicien);
    }

    /**
     * Set dateDebut
     *
     * @param date $dateDebut
     * @return $this
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return date $dateDebut
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param date $dateFin
     * @return $this
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * Get dateFin
     *
     * @return date $dateFin
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set datePrevision
     *
     * @param date $datePrevision
     * @return $this
     */
    public function setDatePrevision($datePrevision)
    {
        $this->datePrevision = $datePrevision;
        return $this;
    }

    /**
     * Get datePrevision
     *
     * @return date $datePrevision
     */
    public function getDatePrevision()
    {
        return $this->datePrevision;
    }

    /**
     * Set rendezvous
     *
     * @param RendezVous $rendezvous
     * @return $this
     */
    public function setRendezvous(RendezVous $rendezvous)
    {
        $this->rendezVous = $rendezvous;
        return $this;
    }

    /**
     * Get rendezvous
     *
     * @return RendezVous $rendezvous
     */
    public function getRendezvous()
    {
        return $this->rendezVous;
    }

    /**
     * Set emailTransmission
     *
     * @param string $emailTransmission
     * @return $this
     */
    public function setEmailTransmission($emailTransmission)
    {
        $this->emailTransmission = $emailTransmission;
        return $this;
    }

    /**
     * Get emailTransmission
     *
     * @return string $emailTransmission
     */
    public function getEmailTransmission()
    {
        return $this->emailTransmission;
    }

    /**
     * Set secondEmailTransmission
     *
     * @param string $secondEmailTransmission
     * @return $this
     */
    public function setSecondEmailTransmission($secondEmailTransmission)
    {
        $this->secondEmailTransmission = $secondEmailTransmission;
        return $this;
    }

    /**
     * Get secondEmailTransmission
     *
     * @return string $secondEmailTransmission
     */
    public function getSecondEmailTransmission()
    {
        return $this->secondEmailTransmission;
    }

    /**
     * Set nomTransmission
     *
     * @param string $nomTransmission
     * @return $this
     */
    public function setNomTransmission($nomTransmission)
    {
        $this->nomTransmission = $nomTransmission;
        return $this;
    }

    /**
     * Get nomTransmission
     *
     * @return string $nomTransmission
     */
    public function getNomTransmission()
    {
        return $this->nomTransmission;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }
}

<?php
namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\RendezVousRepository") @HasLifecycleCallbacks
 */
class RendezVous {

    const COLOR_BORDER_BLUE = '#bce8f1';
    const COLOR_BORDER_YELLOW = '#faebcc';
    const COLOR_BORDER_GREEN = '#d6e9c6';
    const COLOR_BORDER_RED = '#ebccd1';
    const COLOR_BORDER_GREY = '#e1e1e8';

    const COLOR_TEXT_BLUE = '#31708f';
    const COLOR_TEXT_MAROON = '#8a6d3b';
    const COLOR_TEXT_BROWN = '#7d5e09';
    const COLOR_TEXT_GREEN = '#3c763d';
    const COLOR_TEXT_RED = '#a94442';
    const COLOR_TEXT_BLACK = '#333';

    const COLOR_STATUS_BLUE = '#d9edf7';
    const COLOR_STATUS_YELLOW = '#fcf8e3';
    const COLOR_STATUS_GOLD = '#ffd55f';
    const COLOR_STATUS_GREEN = '#dff0d8';
    const COLOR_STATUS_RED = '#f2dede';
    const COLOR_STATUS_WHITE = '#f7f7f9';

    /**
     * @MongoDB\Id(strategy="CUSTOM", type="string", options={"class"="AppBundle\Document\Id\RendezVousGenerator"})
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $titre;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $description;

    /**
     * @MongoDB\Field(type="date")
     * @Assert\NotBlank()
     */
    protected $dateDebut;

    /**
     * @MongoDB\Field(type="date")
     * @Assert\NotBlank()
     * @Assert\Expression(
     *     "this.getDateDebut() <= this.getDateFin()",
     *     message="La date de fin doit être supérieur à la date de début"
     * )
     */
    protected $dateFin;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Compte", inversedBy="lesRendezVous", simple=true)
     */
    protected $participants;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $lieu;

    /**
    * @MongoDB\ReferenceOne(targetDocument="Passage", simple=true)
     */
    protected $passage;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Devis", simple=true)
     */
    protected $devis;

  /**
   * @MongoDB\Field(type="bool")
   */
   protected $rendezVousConfirme;

    public function __construct()
    {
        $this->participants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rendezVousConfirme = true;
    }

    public function getEventJson($backgroundColor) {
        $event = new \stdClass();
        $event->id = $this->getId();
        $event->title = $this->getTitre();
        $event->start = $this->getDateDebut()->format('c');
        $event->end = $this->getDateFin()->format('c');
        $event->textColor = $this->getTextColor();
        $event->backgroundColor =  $this->getStatusColor();
        $event->borderColor = $this->getBorderColor();
        $event->rendezVousConfirme = $this->getRendezVousConfirme();
        return $event;
    }

    public function getBorderColor() {
        if($this->getPlanifiable() && $this->getPlanifiable()->isPlanifie() && !$this->getPlanifiable()->isImprime()) {

            return self::COLOR_BORDER_BLUE;
        }

        if($this->getPlanifiable() && $this->getPlanifiable()->isPlanifie()) {

            return self::COLOR_BORDER_YELLOW;
        }

        if($this->getPlanifiable() && $this->getPlanifiable()->isRealise()) {

            return self::COLOR_BORDER_GREEN;
        }

        if($this->getPlanifiable() && $this->getPlanifiable()->isAnnule()) {

            return self::COLOR_BORDER_RED;
        }

        return self::COLOR_BORDER_GREY;
    }

    public function getTextColor() {

       if($this->getPlanifiable() && ($this->getPlanifiable()->isPlanifie() || $this->getPlanifiable()->isRealise()) && !$this->getPlanifiable()->isSaisieTechnicien()) {

            return self::COLOR_TEXT_BLUE;
        }

        if($this->getPlanifiable() && $this->getPlanifiable()->isPlanifie() && !$this->getPlanifiable()->isImprime() && !$this->getPlanifiable()->isSaisieTechnicien()) {

          return self::COLOR_TEXT_MAROON;
        }

        if($this->getPlanifiable() && ($this->getPlanifiable()->isSaisieTechnicien() && $this->getPlanifiable()->isPdfNonEnvoye())) {

          return self::COLOR_TEXT_BROWN;
        }

        if($this->getPlanifiable() && ($this->getPlanifiable()->isSaisieTechnicien() && !$this->getPlanifiable()->isPdfNonEnvoye())) {

          return self::COLOR_TEXT_GREEN;
        }


        if($this->getPlanifiable() && $this->getPlanifiable()->isAnnule()) {

            return self::COLOR_TEXT_RED;
        }

        return self::COLOR_TEXT_BLACK;
    }

    public function getStatusColor() {

        if($this->getPlanifiable() && ($this->getPlanifiable()->isPlanifie() || $this->getPlanifiable()->isRealise()) && !$this->getPlanifiable()->isSaisieTechnicien()) {

            return self::COLOR_STATUS_BLUE;
        }

        if($this->getPlanifiable() && $this->getPlanifiable()->isPlanifie() && !$this->getPlanifiable()->isImprime() && !$this->getPlanifiable()->isSaisieTechnicien()) {

          return self::COLOR_STATUS_YELLOW;
        }

        if($this->getPlanifiable() && ($this->getPlanifiable()->isSaisieTechnicien() && $this->getPlanifiable()->isPdfNonEnvoye())) {
          return self::COLOR_STATUS_GOLD;
        }

        if($this->getPlanifiable() && ($this->getPlanifiable()->isSaisieTechnicien() && !$this->getPlanifiable()->isPdfNonEnvoye())) {

          return self::COLOR_STATUS_GREEN;
        }

        if($this->getPlanifiable() && $this->getPlanifiable()->isAnnule()) {

            return self::COLOR_STATUS_RED;
        }

        return self::COLOR_STATUS_WHITE;
    }

    public function getParticipantsIds() {
        $participants = array();

        foreach ($this->getParticipants() as $participant) {
            $participants[] = $participant->getId();
        }

        sort($participants);

        return $participants;
    }

    public function pushToPlanifiable() {
        if(!$this->getPlanifiable()) {
            return;
        }
        
        if(count(array_diff($this->getParticipantsIds(), $this->getPlanifiable()->getTechniciensIds())) > 0 || count(array_diff($this->getPlanifiable()->getTechniciensIds(), $this->getParticipantsIds())) > 0) {
            $this->getPlanifiable()->removeAllTechniciens();

            foreach($this->getParticipants() as $participant) {
                $this->getPlanifiable()->addTechnicien($participant);
            }
        }

        $this->getPlanifiable()->setCommentaire($this->getDescription());
        $this->getPlanifiable()->setDateDebut($this->getDateDebut());
        $this->getPlanifiable()->setDateFin($this->getDateFin());
    }




    public function setTimeDebut($time) {
        $dateTime = $this->getDateDebut();
        $this->setDateDebut(new \DateTime($dateTime->format('Y-m-d') . 'T' . $time . ':00'));
    }

    public function setTimeFin($time) {
        $dateTime = $this->getDateFin();
        $this->setDateFin(new \DateTime($dateTime->format('Y-m-d') . 'T' . $time . ':00'));
    }

    public function getTimeDebut() {
        $dateTime = $this->getDateDebut();
        return ($dateTime) ? $dateTime->format('H:i') : null;
    }

    public function getTimeFin() {
        $dateTime = $this->getDateFin();
        return ($dateTime) ? $dateTime->format('H:i') : null;
    }

    public function getDuree() {

        return $this->getDateFin()->diff($this->getDateDebut())->format('%Hh%I');
    }

    /** @MongoDB\PreFlush */
    public function preFlush() {
        $this->pushToPlanifiable();
    }

    /** @MongoDB\PreRemove */
    public function preRemove()
    {
        if(!$this->getPlanifiable()) {
            return;
        }
        $this->getPlanifiable()->deplanifier();
        $this->removePassage();
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set titre
     *
     * @param string $titre
     * @return self
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
        return $this;
    }

    /**
     * Get titre
     *
     * @return string $titre
     */
    public function getTitre()
    {
        return $this->titre;
    }


    /**
     * Set rendezVousConfirme
     *
     * @param boolean $rendezVousConfirme
     * @return self
     */
    public function setRendezVousConfirme($rendezVousConfirme) {
        $this->rendezVousConfirme = $rendezVousConfirme;
        return $this;
    }

    /**
     * Get rendezVousConfirme
     *
     * @return boolean $rendezVousConfirme
     */
    public function getRendezVousConfirme() {
        return $this->rendezVousConfirme;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
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

    /**
     * Set dateDebut
     *
     * @param date $dateDebut
     * @return self
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
     * @return self
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
     * Add participant
     *
     * @param AppBundle\Document\Compte $participant
     */
    public function addParticipant(\AppBundle\Document\Compte $participant)
    {
        $this->participants[] = $participant;
    }

    /**
     * Remove participant
     *
     * @param AppBundle\Document\Compte $participant
     */
    public function removeParticipant(\AppBundle\Document\Compte $participant)
    {
        $this->participants->removeElement($participant);
    }

    /**
     * Get participants
     *
     * @return \Doctrine\Common\Collections\Collection $participants
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    public function removeAllParticipants() {
        $this->participants = new ArrayCollection();
    }

    /**
     * Set lieu
     *
     * @param string $lieu
     * @return self
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;
        return $this;
    }

    /**
     * Get lieu
     *
     * @return string $lieu
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set passage
     *
     * @param AppBundle\Document\Passage $passage
     * @return self
     */
    public function setPassage(\AppBundle\Document\Passage $passage)
    {
        $this->passage = $passage;
        return $this;
    }

    public function removePassage()
    {
        $this->passage = null;
        unset($this->passage);

        return $this;
    }

    public function getPlanifiable()
    {
        if($this->getPassage()){
          return $this->getPassage();
        }
        if($this->getDevis()){
          return $this->getDevis();
        }
        return null;
    }

    /**
     * Get passage
     *
     * @return AppBundle\Document\Passage $passage
     */
    public function getPassage()
    {
        return $this->passage;
    }

    public function setDevis(Devis $devis)
    {
        $this->devis = $devis;
    }

    /**
     * Get passage
     *
     * @return AppBundle\Document\Devis $devis
     */
    public function getDevis()
    {
        return $this->devis;
    }
}

<?php

namespace AppBundle\Model;

interface EtablissementInfosInterface {
    public function getNom();
    public function setNom($nom);
    public function getContact();
    public function setContact($contact);
    public function getAdresse();
    public function setAdresse(\AppBundle\Document\Adresse $adresse);
    public function getType();
    public function setType($type);
    public function getIcon();
    public function getIntitule();
}
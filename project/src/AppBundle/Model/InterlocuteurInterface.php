<?php

namespace AppBundle\Model;

interface InterlocuteurInterface {
    public function getDestinataire();
    public function getAdresse();
    public function getLibelleComplet();
}

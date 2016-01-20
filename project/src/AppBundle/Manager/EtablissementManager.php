<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EtablissementManager
 *
 * @author mathurin
 */

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;

class EtablissementManager {

    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function create() {
      $identfiants = $this->dm->getRepository('AppBundle:Etablissement')->findAllOrderedByIdentifiant();
      var_dump($identfiants->max());
      foreach ($identfiants as $identifiant) {
          
      }
    }

}

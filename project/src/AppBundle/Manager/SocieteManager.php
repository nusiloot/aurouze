<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;

class SocieteManager {

    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }
    
    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Societe');
    }

}

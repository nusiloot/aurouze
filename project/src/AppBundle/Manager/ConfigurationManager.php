<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;

class ConfigurationManager {

    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }
    
    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Configuration');
    }

}

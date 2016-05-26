<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\Configuration;

class ConfigurationManager {

    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function getConfiguration() {

        return $this->getRepository()->findOneById(Configuration::PREFIX);
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Configuration');
    }

}

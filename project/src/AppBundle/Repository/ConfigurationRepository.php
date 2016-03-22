<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class ConfigurationRepository extends DocumentRepository {

    public function findConfiguration() {
        return $this->findOneById("Configuration");
    }


}

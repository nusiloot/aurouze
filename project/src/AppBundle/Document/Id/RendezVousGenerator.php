<?php

namespace AppBundle\Document\Id;

use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Id\UuidGenerator;

class RendezVousGenerator extends UuidGenerator
{

    public function generate(DocumentManager $dm, $document)
    {
        return "RENDEZVOUS-".parent::generate($dm, $document);
    }
}

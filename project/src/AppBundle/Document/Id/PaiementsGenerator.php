<?php

namespace AppBundle\Document\Id;

use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;

class PaiementsGenerator extends AbstractIdGenerator
{

    public function generate(DocumentManager $dm, $document)
    {
        $className = get_class($document);
        $db = $dm->getDocumentDatabase($className);

        if($document->getIdentifiant()) {

            return "PAIEMENTS-".$document->getIdentifiant();
        }

        $document->setIdentifiant(sprintf("%s-%s", $document->getDateCreation()->format('Ymd'),uniqid()));

        return "PAIEMENTS-".$document->getIdentifiant();
    }
}

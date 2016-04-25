<?php

namespace AppBundle\Document\Id;

use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\Passage;

class PassageGenerator extends AbstractIdGenerator
{

    public function generate(DocumentManager $dm, $document)
    {
        $className = get_class($document);
        $db = $dm->getDocumentDatabase($className);

        
        $query = array('_id' => $document->getEtablissement()->getId());
        $newObj = array('$inc' => array('numeroPassageIncrement' => 1));
        
        $command = array();
        $command['findandmodify'] = "Etablissement";
        $command['query'] = $query;
        $command['update'] = $newObj;
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);
        $document->setIdentifiant(sprintf("%s-%s-%05d", $document->getEtablissement()->getIdentifiant(), $document->getDatePrevision()->format('Ymd'), $result['value']['numeroPassageIncrement']));

        return sprintf("%s-%s", Passage::PREFIX, $document->getIdentifiant());
    }
}

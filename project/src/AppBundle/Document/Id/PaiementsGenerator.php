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

        $date = $document->getDateCreation()->format('Y-m-d');
        
        $command = array();
        $command['findandmodify'] = 'doctrine_increment_ids';
        $command['query'] = array('_id' => "Paiments");
        $command['update'] = array('$inc' => array($date => 1));
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);
        
        $document->setIdentifiant(sprintf("%s-%04d", $document->getDateCreation()->format('Ymd'), $result['value'][$date]));
        
        return "PAIEMENTS-".$document->getIdentifiant();
    }
}

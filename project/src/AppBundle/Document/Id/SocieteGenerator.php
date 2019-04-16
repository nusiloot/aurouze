<?php

namespace AppBundle\Document\Id;

use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;

class SocieteGenerator extends AbstractIdGenerator
{

    public function generate(DocumentManager $dm, $document)
    {
        $className = get_class($document);
        $db = $dm->getDocumentDatabase($className);

        if($document->getIdentifiant()) {

            return "SOCIETE-".$document->getIdentifiant();
        }

        $command = array();
        $command['findandmodify'] = 'doctrine_increment_ids';
        $command['query'] = array('_id' => "Societe");
        $command['update'] = array('$inc' => array('current_id' => 1));
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);

        $identifiant = sprintf("%06d", $result['value']['current_id']);
        $document->setIdentifiant($identifiant);
        
        return "SOCIETE-".$document->getIdentifiant();
    }
}

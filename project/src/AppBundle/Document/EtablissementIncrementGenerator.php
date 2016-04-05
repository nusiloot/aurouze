<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;

class EtablissementIncrementGenerator extends AbstractIdGenerator
{

    public function generate(DocumentManager $dm, $document)
    {
        $className = get_class($document);
        $db = $dm->getDocumentDatabase($className);

        $query = array('_id' => $document->getSociete()->getId());
        $newObj = array('$inc' => array('etablissementIncrement' => 1));

        $command = array();
        $command['findandmodify'] = "Societe";
        $command['query'] = $query;
        $command['update'] = $newObj;
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);

        return sprintf("%s-%s-%03d", "ETABLISSEMENT", $document->getSociete()->getIdentifiant(), $result['value']['etablissementIncrement']);
    }
}

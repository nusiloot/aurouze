<?php

namespace AppBundle\Document\Id;

use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;

class ContratGenerator extends AbstractIdGenerator
{
    public function generate(DocumentManager $dm, $document)
    {
        $className = get_class($document);
        $db = $dm->getDocumentDatabase($className);

        $query = array('_id' => $document->getSociete()->getId());
        $newObj = array('$inc' => array('contratIncrement' => 1));

        $command = array();
        $command['findandmodify'] = "Societe";
        $command['query'] = $query;
        $command['update'] = $newObj;
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);

        $document->setIdentifiant(sprintf("%s-%s-%04d", $document->getSociete()->getIdentifiant(), $document->getDateCreation()->format('Ymd'), $result['value']['contratIncrement']));

        $id = sprintf("%s-%s", "CONTRAT", $document->getIdentifiant());

        if($document->getNumeroArchive()) {

            return $id;
        }

        $annee = $document->getDateCreation()->format('y');

        $command = array();
        $command['findandmodify'] = 'doctrine_increment_ids';
        $command['query'] = array('_id' => "ContratArchive");
        $command['update'] = array('$inc' => array($annee => 1));
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);

        $document->setNumeroArchive(sprintf("%s%04d", $annee, $result['value'][$annee]));

        return $id;
    }
}

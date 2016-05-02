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

        $this->updateNumeroArchive($db);

        $command = array();
        $command['findandmodify'] = 'doctrine_increment_ids';
        $command['query'] = array('_id' => "ContratArchive");
        $command['update'] = array('$inc' => array('current_id' => 1));
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);

        $document->setNumeroArchive($result['value']['current_id']);

        return $id;
    }

    public function updateNumeroArchive($db) {
        $command = array();
        $command['aggregate'] = "Contrat";
        $command['pipeline'] = array(array('$group' => array('_id' => 'numero_archive_maxium', 'numeroArchive' => array('$max' => '$numeroArchive'))));
        $result = $db->command($command);
        if(count($result["result"]) > 0) {
            $number = $result["result"][0]['numeroArchive']*1;
            $result = $db->selectCollection('doctrine_increment_ids')->findOne(array('_id' => "ContratArchive"));
            if(isset($result) && $result['current_id'] < $number) {
                $command = array();
                $command['findandmodify'] = 'doctrine_increment_ids';
                $command['query'] = array('_id' => "ContratArchive");
                $command['update'] = array('current_id' => $number);
                $command['upsert'] = true;
                $command['new'] = true;
                $db->command($command);
            }
        }
    }
}

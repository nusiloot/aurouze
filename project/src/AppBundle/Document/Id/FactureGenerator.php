<?php

namespace AppBundle\Document\Id;

use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;

class FactureGenerator extends AbstractIdGenerator
{
    public function generate(DocumentManager $dm, $document)
    {
        $className = get_class($document);
        $db = $dm->getDocumentDatabase($className);

        $query = array('_id' => $document->getSociete()->getId());
        $newObj = array('$inc' => array('factureIncrement' => 1));

        $command = array();
        $command['findandmodify'] = "Societe";
        $command['query'] = $query;
        $command['update'] = $newObj;
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);

        $document->setIdentifiant(sprintf("%s-%s-%04d", $document->getSociete()->getIdentifiant(), $document->getDateEmission()->format('Ymd'), $result['value']['factureIncrement']));

        $id = sprintf("%s-%s", "FACTURE", $document->getIdentifiant());

        if($document->getNumeroFacture()) {

        	return $id;
        }

        if($document->isDevis()) {
            $this->generateNumeroDevis($db, $document);
        } else {
            $this->generateNumeroFacture($db, $document);
        }

        return $id;
    }

    public function generateNumeroDevis($db, $document) {
        if($document->getNumeroDevis()) {
            return;
        }

        $command = array();
        $command['findandmodify'] = 'doctrine_increment_ids';
        $command['query'] = array('_id' => "DevisArchive");
        $command['update'] = array('$inc' => array('current_id' => 1));
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);

        $document->setNumeroDevis($result['value']['current_id']);
    }

    public function generateNumeroFacture($db, $document) {
        if($document->getNumeroFacture()) {
            return;
        }

        $annee = $document->getDateFacturation()->format('Y');

        $command = array();
        $command['findandmodify'] = 'doctrine_increment_ids';
        $command['query'] = array('_id' => "FactureArchive");
        $command['update'] = array('$inc' => array($annee => 1));
        $command['upsert'] = true;
        $command['new'] = true;
        $result = $db->command($command);

        $document->setNumeroFacture(sprintf("%s%04d", $annee, $result['value'][$annee]));
    }
}

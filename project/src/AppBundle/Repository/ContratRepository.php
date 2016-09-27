<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MongoDate as MongoDate;
use AppBundle\Manager\ContratManager;
use AppBundle\Repository\SocieteRepository;

class ContratRepository extends DocumentRepository {

    public function findBySociete($societe) {
        return $this->findBy(
                        array('societe' => $societe->getId()),
                        array('dateFin' => 'DESC'));
    }

    public function findByEtablissement($etablissement) {
        return $this->findBy(
                        array('societe' => $etablissement->getSociete()->getId()),
                        array('dateFin' => 'DESC'));
    }

    public function findContratMouvements($societe, $isFacturable, $isFacture) {
        return $this->createQueryBuilder()
             ->field('mouvements.societe')->equals($societe->getId())
             ->field('mouvements.facturable')->equals($isFacturable)
             ->field('mouvements.facture')->equals($isFacture)
             ->getQuery()
             ->execute();
    }

    public function findLast() {
    	return $this->findBy(array(), array('dateCreation' => 'DESC'), 50);
    }

    public function findAllSortedByNumeroArchive() {
       return $this->findBy(array(), array('numeroArchive' => 'ASC'));
    }

    public function countContratByCommercial($compte) {

        return $this->createQueryBuilder()
             ->field('commercial')->equals($compte->getId())
             ->getQuery()->execute()->count();
    }

    public function findAllFrequences() {
    	return ContratManager::$frequences;
    }

    public function findByQuery($q)
    {
        $q = str_replace(",", "", $q);
        $q = "\"".str_replace(" ", "\" \"", $q)."\"";
    	$resultSet = array();
    	$itemResultSet = $this->getDocumentManager()->getDocumentDatabase('AppBundle:Contrat')->command([
    			'find' => 'Contrat',
    			'filter' => ['$text' => ['$search' => $q]],
    			'projection' => ['score' => [ '$meta' => "textScore" ]],
    			'sort' => ['score' => [ '$meta' => "textScore" ]],
    			'limit' => 100

    	]);
    	if (isset($itemResultSet['cursor']) && isset($itemResultSet['cursor']['firstBatch'])) {
    		foreach ($itemResultSet['cursor']['firstBatch'] as $itemResult) {
    			$resultSet[] = array("doc" => $this->uow->getOrCreateDocument('\AppBundle\Document\Contrat', $itemResult), "score" => $itemResult['score']);
    		}
    	}
    	return $resultSet;
    }

    public function findByDateEntreDebutFin(\DateTime $date) {
        $q = $this->createQueryBuilder();
          $q->field('dateDebut')->lte($date);
          $q->field('dateFin')->gte($date);
        /**
        * Attention cette requete devrait probablement renvoyer aussi les contrats a date de fin null?
        */
        // $q->addOr($q->expr()->field('dateFin')->gte($date))
        //   ->addOr($q->expr()->field('dateFin')->equals(null));

        $query = $q->getQuery();

        return $query->execute();
    }

    public function findLastContratByNumero($numeroArchive) {
        $q = $this->createQueryBuilder();
        $q->field('numeroArchive')->equals($numeroArchive);
        $q->sort('dateFin', 'desc');
        $q->limit(1);
        $query = $q->getQuery();

        foreach($query->execute() as $contrat) {

            return $contrat;
        }

        return null;
    }

    public function findContratsAReconduire($typeContrat = null, \DateTime $date, $societe = null) {
          $date = new \DateTime($date->format('Y-m-d')." 23:59:59");
          $q = $this->createQueryBuilder();
          if ($societe) {
			$societeRepo = $this->getDocumentManager()->getRepository('AppBundle:Societe');
			$q->field('societe')->in($societeRepo->getIdsByQuery($societe));
          }
          if ($typeContrat) {
          	$q->field('typeContrat')->equals($typeContrat);
          } else {
          	$q->field('typeContrat')->in(array_keys(ContratManager::$types_contrats_reconductibles));
          }
          $q->field('dateFin')->lte($date);
          $q->field('reconduit')->equals(false);
          $q->field('statut')->notEqual(ContratManager::STATUT_EN_ATTENTE_ACCEPTATION);
          $q->sort('dateFin', 'desc');
          $query = $q->getQuery();

        return $query->execute();
    }

}

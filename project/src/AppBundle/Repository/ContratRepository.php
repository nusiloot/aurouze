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
    	return $this->findBy(array(), array('dateCreation' => 'DESC'), 30);
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

    public function findContratsAReconduire($typeContrat = ContratManager::TYPE_CONTRAT_RECONDUCTION_TACITE, \DateTime $date, $societe = null) {
          $q = $this->createQueryBuilder();
          if ($societe) {
			$societeRepo = $this->getDocumentManager()->getRepository('AppBundle:Societe');
			$q->field('societe')->in($societeRepo->getIdsByQuery($societe));
          }
          $q->field('typeContrat')->equals($typeContrat);
          $q->field('dateFin')->lte($date);
          $q->field('reconduit')->equals(false);
          $q->field('statut')->notEqual(ContratManager::STATUT_EN_ATTENTE_ACCEPTATION);
          $q->sort('dateFin', 'asc');
          $query = $q->getQuery();

        return $query->execute();
    }

}

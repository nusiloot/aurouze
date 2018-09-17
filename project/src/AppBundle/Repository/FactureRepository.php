<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Tool\RechercheTool;
use AppBundle\Document\societe;

/**
 * FactureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FactureRepository extends DocumentRepository {

    public function findAllByContrat($contrat) {

        return $this->createQueryBuilder()
                        ->field('lignes.origineDocument.$id')->equals($contrat->getId())
                        ->getQuery()
                        ->execute();
    }

    public function findByTerms($queryString, $filter = false, $withCloture = false) {
        $terms = explode(" ", trim(preg_replace("/[ ]+/", " ", $queryString)));
        $results = null;
        foreach ($terms as $term) {
            if (strlen($term) < 2) {
                continue;
            }
            $q = $this->createQueryBuilder();
			if (!$withCloture) {
            	$q->field('cloture')->equals(false);
			}
            $q->field('numeroFacture')->notEqual(null);
            if (preg_match('/^[0-9]+\.[0-9]+$/', $term)) {
                $nbInf = $term - 0.0001;
                $nbSup = $term + 0.0001;
                $q->addOr($q->expr()->field('montantTTC')->lt($nbSup)->gt($nbInf))
                  ->addOr($q->expr()->field('montantAPayer')->lt($nbSup)->gt($nbInf));
            } else {
                $q->addOr($q->expr()->field('destinataire.nom')->equals(new \MongoRegex('/.*' . RechercheTool::getCorrespondances($term) . '.*/i')))
                        ->addOr($q->expr()->field('numeroFacture')->equals(new \MongoRegex('/.*' . $term . '.*/i')));
            }
            if($filter && !$withCloture){
              $q->field('avoir')->equals(null);
            }
            $factures = $q->limit(1000)->getQuery()->execute();

            $currentResults = array();
            foreach ($factures as $facture) {
                $currentResults[$facture->getId()] = $facture->__toString();
            }

            if (!is_null($results)) {
                $results = array_intersect_assoc($results, $currentResults);
            } else {
                $results = $currentResults;
            }
        }

        return is_null($results) ? array() : $results;
    }

    public function exportOneMonthByDate(\DateTime $dateDebut,\DateTime $dateFin) {

        $q = $this->createQueryBuilder();

        $q->field('dateFacturation')->gte($dateDebut)->lte($dateFin)->sort('dateFacturation', 'asc');
        $query = $q->getQuery();

        return $query->execute();
    }

    public function exportByPrelevements($clients) {

        $date = new \DateTime();
        $date->modify("-1 year");
    	$q = $this->createQueryBuilder();
    	$q->addAnd($q->expr()->field('societe')->in($clients));
    	$q->addAnd($q->expr()->field('cloture')->equals(false));
        $q->addAnd($q->expr()->field('montantHT')->gt(0.0));
        $q->addAnd($q->expr()->field('avoir')->equals(null));
        $q->addAnd($q->expr()->field('inPrelevement')->equals(null));
        $q->addAnd($q->expr()->field('dateEmission')->gt($date));
    	$query = $q->getQuery();
    	return $query->execute();
    }

    public function exportBySocieteAndDate($societe, \DateTime $dateDebut,\DateTime $dateFin) {

        $q = $this->createQueryBuilder();
        $q->field('societe')->equals($societe->getId());
        $q->field('dateFacturation')->gte($dateDebut)->lte($dateFin)->sort('dateFacturation', 'asc');
        $query = $q->getQuery();

        return $query->execute();
    }

    public function findByQuery($q)
    {
        $q = "\"".str_replace(" ", "\" \"", $q)."\"";
    	$resultSet = array();
    	$itemResultSet = $this->getDocumentManager()->getDocumentDatabase('AppBundle:Facture')->command([
    			'find' => 'Facture',
    			'filter' => ['$text' => ['$search' => $q]],
    			'projection' => ['score' => [ '$meta' => "textScore" ]],
    			'sort' => ['score' => [ '$meta' => "textScore" ]],
    			'limit' => 100

    	]);
    	if (isset($itemResultSet['cursor']) && isset($itemResultSet['cursor']['firstBatch'])) {
    		foreach ($itemResultSet['cursor']['firstBatch'] as $itemResult) {
    			$resultSet[] = array("doc" => $this->uow->getOrCreateDocument('\AppBundle\Document\Facture', $itemResult), "score" => $itemResult['score']);
    		}
    	}
    	return $resultSet;
    }

    public function findFactureRetardDePaiement($dateFactureBasse = null, $dateFactureHaute = null, $nbRelance = null, $societe = null, $commercial = null){
      $today = new \DateTime();
      $q = $this->createQueryBuilder();
      $q->field('numeroFacture')->notEqual(null);
      $q->field('cloture')->equals(false);
      $q->field('montantTTC')->gt(0.0);
      //$q->field('montantAPayer')->gt(0.0);
      $q->field('avoir')->equals(null);
      $q->field('dateLimitePaiement')->lte($today);
      if($dateFactureBasse){
        $q->field('dateFacturation')->gte($dateFactureBasse);
      }
      if($dateFactureHaute){
        $q->field('dateFacturation')->lte($dateFactureHaute);
      }
      if(!is_null($nbRelance)){
        if($nbRelance > 2){
          $q->field('nbRelance')->gte($nbRelance);
        }elseif ($nbRelance == 1 || $nbRelance == 2) {
          $q->field('nbRelance')->equals($nbRelance);
        }elseif ($nbRelance == 0) {
          $q->addOr($q->expr()->field('nbRelance')->equals(null))
            ->addOr($q->expr()->field('nbRelance')->equals(0));
        }
      }
      	if ($societe && !preg_match('/^SOCIETE-[0-9]*$/', $societe)) {
      		$societeRepo = $this->getDocumentManager()->getRepository('AppBundle:Societe');
      		$societeTab = explode(', ', $societe);
      		$societe = $societeRepo->findOneBy(array('identifiant' => $societeTab[count($societeTab)-1]));
        	$q->field('societe')->equals($societe->getId());
      	} elseif ($societe) {
        	$q->field('societe')->equals($societe);
      	}
        $q->sort('dateFacturation', 'desc')->sort('societe', 'asc');
        $query = $q->getQuery();
        $results = $query->execute();
        $factures = array();
        foreach($results as $facture) {
        	if ($commercial) {
        		if (!$facture->getContrat()) {
        			continue;
        		} else {
        			if (!$facture->getContrat()->getCommercial()) {
        				continue;
        			} else {
        				if ($facture->getContrat()->getCommercial()->getId() != $commercial->getId()) {
        					continue;
        				}
        			}
        		}
        	}
            $factures[$facture->getId()] = $facture;
        }

        return $factures;
    }

    public function findRetardDePaiementBySociete(Societe $societe, $nbJourSeuil = 0){
      $jour = new \DateTime();
      $jour->modify("-".$nbJourSeuil." days");
      $q = $this->createQueryBuilder();
      $q->field('numeroFacture')->notEqual(null);
      $q->field('societe')->equals($societe->getId());
      $q->field('cloture')->equals(false);
      $q->field('montantTTC')->gt(0.0);
      $q->field('avoir')->equals(null);
      $q->field('dateLimitePaiement')->lt($jour)->sort('dateFacturation', 'asc');
      $query = $q->getQuery();
      return $query->execute();
    }

}

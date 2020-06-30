<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\Societe;
use AppBundle\Tool\RechercheTool;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SocieteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SocieteRepository extends DocumentRepository {

    public function findByTerms($queryString, $withNonActif = false, $limit = 1000, $mixWith = false) {
        $terms = explode(" ", trim(preg_replace("/[ ]+/", " ", $queryString)));

        $results = array();
        foreach ($terms as $term) {
            if (strlen($term) < 2) {
                continue;
            }
            $q = $this->createQueryBuilder();
            $q->addOr($q->expr()->field('identifiant')->equals(new \MongoRegex('/.*' . $term . '.*/i')))
              ->addOr($q->expr()->field('raisonSociale')->equals(new \MongoRegex('/.*' . RechercheTool::getCorrespondances($term) . '.*/i')))
              ->addOr($q->expr()->field('adresse.adresse')->equals(new \MongoRegex('/.*' . RechercheTool::getCorrespondances($term) . '.*/i')))
              ->addOr($q->expr()->field('adresse.codePostal')->equals(new \MongoRegex('/.*' . $term . '.*/i')))
              ->addOr($q->expr()->field('adresse.commune')->equals(new \MongoRegex('/.*' . RechercheTool::getCorrespondances($term) . '.*/i')));
            if (!$withNonActif) {
                $q->field('actif')->equals(true);
            }
            $societes = $q->limit($limit)->getQuery()->execute();

            $currentResults = array();
            foreach ($societes as $societe) {
								if($mixWith){
									$currentResults[$societe->getId()] = array("doc" => $societe, "score" => "1", "instance" => "Societe");
								}else{
                	$currentResults[$societe->getId()] = $societe->getIntitule();
								}
            }
						if (count($results) > 0) {
								$results = array_merge($results, $currentResults);
						} else {
								$results = $currentResults;
						}
        }
        //$etablissements = $this->dm->getRepository('Etablissement')->findByTerms($queryString);
        //$results = array_merge($results, $etablissements);
        return is_null($results) ? array() : $results;
    }


    public function findByQuery($q, $inactif = false, $limit = 150)
    {
        $q = str_replace(",", "", $q);
        $q = "\"".str_replace(" ", "\" \"", $q)."\"";

    	$resultSet = array();
    	$filter = ($inactif)? ['$text' => ['$search' => $q]] : ['$text' => ['$search' => $q], "actif" => true] ;
    	$itemResultSet = $this->getDocumentManager()->getDocumentDatabase('AppBundle:Societe')->command([
    		'find' => 'Societe',
    		'filter' => $filter,
    		'projection' => ['score' => [ '$meta' => "textScore" ]],
    		'sort' => ['score' => [ '$meta' => "textScore" ]],
    		'limit' => $limit

        ]);
    	if (isset($itemResultSet['cursor']) && isset($itemResultSet['cursor']['firstBatch'])) {
	    	foreach ($itemResultSet['cursor']['firstBatch'] as $itemResult) {
				$docSoc = $this->uow->getOrCreateDocument('\AppBundle\Document\Societe', $itemResult);
	    		$resultSet[$docSoc->getId()] = array("doc" => $docSoc, "score" => $itemResult['score'], "instance" => "Societe");
	    	}
    	}
    	return $resultSet;
    }

    public function findByIdentifiantReprises(int $idReprise){
        return $this->findBy(array('identifiantReprise' => $idReprise));
    }



    public function findByElasticQuery($service, $q, $inactif = false, $limit = 150)
    {
    	$q = str_replace(",", " ", $q);
    	$keywords = explode(" ", $q);
    	$q = '';
    	foreach ($keywords as $keyword) {
    		$q .= "$keyword* ";
    	}
    	if (!$inactif) {
    		$q .= "actif:true";
    	}

    	$query = new \Elastica\Query\QueryString();
    	$query->setDefaultOperator('AND');
    	$query->setQuery($q);

    	$resultSet = array();
    	$results = $service->find($query, $limit);
    	foreach ($results as $result) {
    		$resultSet[$result->getId()] = array("doc" => $result, "score" => 1, "instance" => join('', array_slice(explode('\\', get_class($result)), -1)));
    	}
    	return $resultSet;
    }


    public function getSocieteIdsWithIban()
    {
    	$ids = array();
    	$q = $this->createQueryBuilder();
        $q->addAnd($q->expr()->field('sepa.iban')->exists(true));
        $q->addAnd($q->expr()->field('sepa.actif')->equals(true));
        $query = $q->getQuery();
        $items = $query->execute();
        foreach ($items as $item) {
        	$ids[] = $item->getId();
        }
        return $ids;
    }

    public function findAllTags() {
        $request = $this->createQueryBuilder()
                ->distinct('tags')
                ->hydrate(false)
                ->getQuery()
                ->execute();
        return $request->toArray();
    }

    public function findByTag($tag)
    {
        $request = $this->createQueryBuilder('Societe')
                        ->field('tags')->in([$tag])
                        ->select('identifiant', 'adresse', 'type', 'raisonSociale', 'contactCoordonnee')
                        ->readOnly()
                        ->getQuery()
                        ->execute();

        return $request->toArray();
    }

    public function getIdsByIban() {
    	$ids = array();
    	foreach ($items as $item) {
    		$obj = $item["doc"];
    		$ids[] = $obj->getId();
    	}
    	return $ids;
    }

    public function findAllPassages($societe) {
        $societe = $this->findOneById($societe->getId());
        $passagesArray = array();
        foreach ($societe->getEtablissements() as $etb) {
            $passages = $this->dm->getRepository('AppBundle:Passage')->findByEtablissement($etb->getId());
            foreach ($passages as $passage) {
                $passagesArray[$passage->getId()] = $passage;
            }
        }
        return $passagesArray;
    }
    public function findAllFrequencePaiement($value){
        return $this->findBy(array('frequencePaiement' => $value));
    }

}

<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MongoDate as MongoDate;

class ContratRepository extends DocumentRepository 
{

	public function findByEtablissement($etablissement) 
	{
		return $etablissement->getContrats();
	}
	
	public function findNextNumero($etablissement) 
	{
	
		$contrats = $this->findByEtablissement($etablissement);
	
		$identifiants = array();
		foreach ($contrats as $contrat) {
			$identifiants[$contrat->getIdentifiant()] = $contrat->getIdentifiant();
		}
		return (count($identifiants) > 0)? (substr(max($identifiants),-6) + 1) : null;
	}

}

<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\Compte;
use AppBundle\Manager\CompteManager;

class CompteRepository extends DocumentRepository {

    public function findAllUtilisateurs() {
        $societe = $this->dm->getRepository('AppBundle:Societe')->findOneByRaisonSociale("AUROUZE");
        return $this->findBy(array('societe' => $societe->getId()));
    }

    public function findAllUtilisateursTechnicien() {
        return $this->findAllUtilisateursHasTag(CompteManager::TYPE_TECHNICIEN);
    }

    public function findAllUtilisateursCommercial() {
        return $this->findAllUtilisateursHasTag(CompteManager::TYPE_COMMERCIAL);
    }

    public function findAllUtilisateursCalendrier() {
        return $this->findAllUtilisateursHasTag(CompteManager::TAG_CALENDRIER);
    }

    public function findAllUtilisateursHasTag($tag) {
        $compteUtilisateurs = $this->findAllUtilisateurs();
        $utilisateurs = array();
        foreach ($compteUtilisateurs as $utilisateur) {
            if ($utilisateur->hasTag($tag)) {
                $utilisateurs[$utilisateur->getId()] = $utilisateur;
            }
        }
        return $utilisateurs;
    }

    public function findAllUtilisateursActif() {
        $compteUtilisateurs = $this->findAllUtilisateurs();
        $utilisateurs = array();
        foreach ($compteUtilisateurs as $utilisateur) {
            if ($utilisateur->isActif()) {
                $utilisateurs[$utilisateur->getId()] = $utilisateur;
            }
        }
        return $utilisateurs;
    }

    public function findByQuery($q, $inactif = false)
    {
        $q = str_replace(",", "", $q);
        $q = "\"".str_replace(" ", "\" \"", $q)."\"";
    	$resultSet = array();
    	$itemResultSet = $this->getDocumentManager()->getDocumentDatabase('AppBundle:Societe')->command([
    			'find' => 'Compte',
    			'filter' => ['$text' => ['$search' => $q]],
    			'projection' => ['score' => [ '$meta' => "textScore" ]],
    			'sort' => ['score' => [ '$meta' => "textScore" ]],
    			'limit' => 50

    	]);
    	if (isset($itemResultSet['cursor']) && isset($itemResultSet['cursor']['firstBatch'])) {
    		foreach ($itemResultSet['cursor']['firstBatch'] as $itemResult) {
    			if (!$inactif && !$itemResult['actif']) {
    				continue;
    			}
    			$resultSet[] = array("doc" => $this->uow->getOrCreateDocument('\AppBundle\Document\Compte', $itemResult), "score" => $itemResult['score'], "instance" => "Compte");
    		}
    	}
    	return $resultSet;
    }

}

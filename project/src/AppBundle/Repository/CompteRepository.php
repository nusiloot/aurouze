<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\Compte;
use AppBundle\Manager\CompteManager;

class CompteRepository extends DocumentRepository {

    public function findAllUtilisateurs() {
        $societe = $this->dm->getRepository('AppBundle:Societe')->findOneByRaisonSociale("AUROUZE");
        return $this->findBy(array('societe.id' => $societe->getId()));
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

}

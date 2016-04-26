<?php

namespace AppBundle\Repository;
use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\Compte;

class CompteRepository extends DocumentRepository {

    public function findAllByType($type) {
        return $this->findAll(); //$this->findBy(array('type' => $type));
    }
    
    public function findAllActif() {
    	return $this->findBy(array('actif' => true));
    }
    
    public function findByIdentifiant($identifiant) {
    	return $this->find(Compte::PREFIX.'-'.$identifiant);
    }
    
    public function findByIdentite($identite) {
    	return $this->findOneBy(array('identite' => $identite));
    }
    
    public function findAllByTypeArray($type) {
        $comptes = $this->findAllByType($type);
        $result = array();
        foreach ($comptes as $compte) {
            $result[$compte->getIdentifiant()] = $compte;
        }
        return $result;
    }
    
    public function findAllInArray() {
        $comptes = $this->findAll();
        $result = array();
        foreach ($comptes as $compte) {
            $result[$compte->getIdentifiant()] = $compte;
        }
        return $result;
    }
    

}

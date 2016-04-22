<?php

namespace AppBundle\Repository;
use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\User;

class UserRepository extends DocumentRepository {

    public function findAllByType($type) {
        return $this->findBy(array('type' => $type));
    }
    
    public function findByIdentifiant($identifiant) {
    	return $this->find(User::PREFIX.'-'.$identifiant);
    }
    
    public function findByIdentite($identite) {
    	return $this->findOneBy(array('identite' => $identite));
    }
    
    public function findAllByTypeArray($type) {
        $users = $this->findAllByType($type);
        $result = array();
        foreach ($users as $user) {
            $result[$user->getIdentifiant()] = $user;
        }
        return $result;
    }
    
    public function findAllInArray() {
        $users = $this->findAll();
        $result = array();
        foreach ($users as $user) {
            $result[$user->getIdentifiant()] = $user;
        }
        return $result;
    }
    

}

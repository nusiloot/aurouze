<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\Societe;

class PaiementsRepository extends DocumentRepository {

 
    
    public function findPaiementsByFacture($facture){   
        return $this->createQueryBuilder()
             ->select('paiement')
             ->field('paiement.facture')
             ->equals($facture->getId())
             ->getQuery()
             ->getIterator();
    }
    
    
    public function getLastPaiements($nbLimit) {
       return $this->createQueryBuilder()
             ->sort('dateCreation','desc')
             ->limit($nbLimit)
             ->getQuery()
             ->execute();
        
        
    }
    
    public function getBySociete(Societe $societe) {
        return $this->createQueryBuilder()
             ->field('paiement.facture')->equals(new \MongoRegex('/^FACTURE-' .$societe->getIdentifiant(). '.*/i'))
             ->sort('dateCreation','desc')
             ->getQuery()
             ->execute();
    }

}

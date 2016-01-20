<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * EtablissementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EtablissementRepository extends DocumentRepository
{
    
     public function findAllOrderedByIdentifiant()
    {
        return $this->createQueryBuilder()
            ->sort('identifiant', 'ASC')
            ->getQuery()
            ->execute();
    }
}
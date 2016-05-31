<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\RendezVous;

class RendezVousRepository extends DocumentRepository
{
    public function findByDateAndParticipant($startDate, $endDate, $participant) {
        $mongoStartDate = new \MongoDate(strtotime($startDate." 00:00:00"));
        $mongoEndDate = new \MongoDate(strtotime($endDate." 00:00:00"));

        $query = $this->createQueryBuilder('RendezVous');
        $query->addOr($query->expr()->field('dateDebut')->gte($mongoStartDate)->field('dateDebut')->lte($mongoEndDate));
        $query->addOr($query->expr()->field('dateFin')->gte($mongoStartDate));
        $query->field('participants')->equals($participant->getId())
                ->sort('dateDebut', 'asc');

        return $query->getQuery()->execute();
    }

    public function findByDate($startDate, $endDate) {
        $mongoStartDate = new \MongoDate(strtotime($startDate." 00:00:00"));
        $mongoEndDate = new \MongoDate(strtotime($endDate." 00:00:00"));
        
        $query = $this->createQueryBuilder('RendezVous');
        $query->addOr($query->expr()->field('dateDebut')->gte($mongoStartDate)->field('dateDebut')->lte($mongoEndDate));
        $query->addOr($query->expr()->field('dateFin')->gte($mongoStartDate));
        $query->sort('dateDebut', 'asc');

        return $query->getQuery()->execute();
    }
}

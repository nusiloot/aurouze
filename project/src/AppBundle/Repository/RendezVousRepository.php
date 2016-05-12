<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\RendezVous;

class RendezVousRepository extends DocumentRepository
{
    public function findByDateAndParticipant($startDate, $endDate, $participant) {
        $mongoStartDate = new \MongoDate(strtotime($startDate." 00:00:00"));
        $mongoEndDate = new \MongoDate(strtotime($endDate." 23:59:59"));
        $query = $this->createQueryBuilder('RendezVous')
                ->field('dateDebut')->gte($mongoStartDate)
                ->field('dateDebut')->lte($mongoEndDate)
                ->field('participants')->equals($participant->getId())
                ->sort('dateDebut', 'asc')
                ->getQuery();
        return $query->execute();
    }
}

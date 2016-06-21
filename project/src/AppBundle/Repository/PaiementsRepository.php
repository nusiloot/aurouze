<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\Societe;

class PaiementsRepository extends DocumentRepository {

    public function findPaiementsByFacture($facture) {
        return $this->createQueryBuilder()
                        ->select('paiement')
                        ->field('paiement.facture')
                        ->equals($facture->getId())
                        ->getQuery()
                        ->getIterator();
    }

    public function getLastPaiements($nbLimit) {
        return $this->createQueryBuilder()
                        ->sort('dateCreation', 'desc')
                        ->limit($nbLimit)
                        ->getQuery()
                        ->execute();
    }

    public function getBySociete(Societe $societe) {
        return $this->createQueryBuilder()
                        ->field('paiement.facture')->equals(new \MongoRegex('/^FACTURE-' . $societe->getIdentifiant() . '.*/i'))
                        ->sort('dateCreation', 'desc')
                        ->getQuery()
                        ->execute();
    }

    public function findByDate(\DateTime $date) {

        $oneMonthPast = clone $date;
        $oneMonthPast->modify("-1 month");
        $q = $this->createQueryBuilder();

        $q->field('paiement.datePaiement')->gte(new \DateTime("2016-05-01"));
        $q->field('paiement.datePaiement')->lte(new \DateTime("2016-05-31"));
        $query = $q->getQuery();

        return $query->execute();
    }


    public function findLastMonthByDate(\DateTime $date) {

        $oneMonthPast = clone $date;
        $oneMonthPast->modify("-1 month");
        $startOfMonth = \DateTime::createFromFormat('Y-m-d', $oneMonthPast->format('Y-m')."-01");
        $endOfMonth = \DateTime::createFromFormat('Y-m-d', $date->format('Y-m')."-01");
        $q = $this->createQueryBuilder();
        $q->field('paiement.datePaiement')->gte($startOfMonth);
        $q->field('paiement.datePaiement')->lt($endOfMonth);
        $query = $q->getQuery();

        return $query->execute();
    }
}

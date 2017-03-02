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

    public function findByDate(\DateTime $dateFrom,\DateTime $dateTo) {

        $q = $this->createQueryBuilder();

        $q->field('paiement.datePaiement')->gte($dateFrom);
        $q->field('paiement.datePaiement')->lte($dateTo);

        $query = $q->getQuery();

        return $query->execute();
    }


    public function findByDatePaiementsDebutFin(\DateTime $dateDebut,\DateTime $dateFin) {

        $q = $this->createQueryBuilder();
        $q->field('paiement.datePaiement')->gte($dateDebut);
        $q->field('paiement.datePaiement')->lte($dateFin);
        $query = $q->getQuery();

        return $query->execute();
    }
    


    public function findByPeriode($periode, $limit) {
    	if (!preg_match('/^([0-9]{2})\/([0-9]{4})$/', $periode, $items)) {
            return array();
        }
        $dateDebut = new \DateTime($items[2].'-'.$items[1].'-01');
        $dateFin = new \DateTime($items[2].'-'.$items[1].'-'.$dateDebut->format('t'));
    	$q = $this->createQueryBuilder();
    	$q->field('dateCreation')->gte($dateDebut);
    	$q->field('dateCreation')->lte($dateFin);
    	$q->sort('dateCreation', 'desc');
    	$query = $q->getQuery();
        return $query->execute();
    }
}

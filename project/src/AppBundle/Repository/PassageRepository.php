<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Manager\PassageManager;

/**
 * EtablissementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
use MongoDate as MongoDate;

class PassageRepository extends DocumentRepository {

    public function findAllPlanifieByPeriodeAndIdentifiantTechnicien($startDate, $endDate, $technicien) {
        $mongoStartDate = new MongoDate(strtotime($startDate." 00:00:00"));
        $mongoEndDate = new MongoDate(strtotime($endDate." 23:59:59"));
        $query = $this->createQueryBuilder('Passage')
                ->field('dateDebut')->gte($mongoStartDate)
                ->field('dateDebut')->lte($mongoEndDate)
                ->field('dateFin')->gte($mongoStartDate)
                ->field('techniciens')->equals($technicien->getId())
                ->sort('dateDebut', 'asc')
                ->getQuery();
        return $query->execute();
    }

    public function findAllPlanifieByPeriode($startDate, $endDate) {
        $mongoStartDate = new MongoDate(strtotime($startDate));
        $mongoEndDate = new MongoDate(strtotime($endDate));
        $query = $this->createQueryBuilder('Passage')
                ->field('dateDebut')->gte($mongoStartDate)
                ->field('dateDebut')->lte($mongoEndDate)
                ->field('dateFin')->gte($mongoStartDate)
    			->sort('technicien', 'desc')
    			->sort('dateDebut', 'asc')
                ->getQuery();
        return $query->execute();
    }

    public function findHistoriqueByEtablissementAndPrestations($etablissement, $prestations = array(), $limit = 2) {
        $passagesHistorique = array();

        foreach($prestations as $prestation) {
            $passages = $this->findBy(array('etablissement' => $etablissement->getId(), 'statut' => PassageManager::STATUT_REALISE, 'prestations.identifiant' => $prestation->getIdentifiant()), array('dateDebut' => 'DESC'), $limit);
            foreach($passages as $passage) {
                $passagesHistorique[$passage->getDateDebut()->format('YmdHi')."_".$passage->getId()] = $passage;
            }
        }

        return $passagesHistorique;
    }

    public function findOneByIdentifiantPassage($identifiantPassage) {

        return $this->findOneBy(
                        array('id' => 'PASSAGE-' . $identifiantPassage));
    }

    public function findOneByIdentifiantEtablissementAndIdentifiantPassage($identifiantEtb, $identifiantPassage) {

        return $this->findOneBy(
                        array('id' => 'PASSAGE-' . $identifiantEtb . '-' . $identifiantPassage));
    }

    public function findByEtablissementAndCreateAt($etablissementIdentifiant, $createAt) {
        return $this->findBy(
                        array('etablissementIdentifiant' => $etablissementIdentifiant, 'createAt' => $createAt));
    }



    public function findPassagesForEtablissement($etablissementIdentifiant) {
    	$query = $this->createQueryBuilder('Passage')
    	->field('etablissementIdentifiant')->equals($etablissementIdentifiant)
    	->sort('datePrevision', 'desc')
    	->getQuery();
    	return$query->execute();
    }

    public function findPassagesForEtablissementSortedByContrat($etablissementIdentifiant) {
    	$query = $this->createQueryBuilder('Passage')
    	->field('etablissementIdentifiant')->equals($etablissementIdentifiant)
    	->sort('contratId', 'desc')->sort('dateCreation', 'desc')
    	->getQuery();
    	return $query->execute();
    }

    public function findTechniciens() {
    	$techniciens = array();
    	$date = new \DateTime();
    	$mongoEndDate = new MongoDate(strtotime($date->format('Y-m-d')));
    	$date->modify('-2 month');
    	$mongoStartDate = new MongoDate(strtotime($date->format('Y-m-d')));
    	$query = $this->createQueryBuilder('Passage')
    	->field('dateFin')->gte($mongoStartDate)
    	->field('dateFin')->lte($mongoEndDate)
    	->group(array('technicien' => 1), array('count' => 0))
    	->reduce('function (obj, prev) { prev.count++; }')
    	->getQuery();
    	$result =  $query->execute();

    	if (count($result)) {
    		foreach ($result as $item) {
    			$techniciens[$item['technicien']] = $item['technicien'];
    		}
    	}

        ksort($techniciens);

    	return $techniciens;
    }

    public function findToPlan() {
        $date= new \DateTime();
        $twoMonth = clone $date;
        $twoMonth->modify("+1 month");
     //   $mongoStartDate = new MongoDate(strtotime($date->format('Y-m-d')));

        $mongoEndDate = new MongoDate(strtotime($twoMonth->format('Y-m-d')));

        $query = $this->createQueryBuilder('Passage')
                      ->field('statut')->equals(PassageManager::STATUT_A_PLANIFIER)
                // A enlever
                     //  ->field('datePrevision')->gte($mongoStartDate)

                      ->field('datePrevision')->lte($mongoEndDate)
                      ->sort('datePrevision', 'asc')
                      ->getQuery();

        return $query->execute();
    }
    
    public function getNbPassagesToPlanPerMonth() {
        $passages = $this->findToPlan();
        $result = array();
        foreach ($passages as $passage) {
            $moisAnnee = $passage->getDatePrevision()->format('Ym');
            if(!array_key_exists($moisAnnee, $result)){
                $result[$moisAnnee] = new \stdClass();
                $result[$moisAnnee]->nb = 0;
                $result[$moisAnnee]->date = $passage->getDatePrevision();
            }
            $result[$moisAnnee]->nb = $result[$moisAnnee]->nb + 1;
        }
        return $result;
    }

    public function countPassagesByTechnicien($compte) {

        return $this->createQueryBuilder()
             ->field('techniciens')->equals($compte->getId())
             ->getQuery()->execute()->count();
    }

}

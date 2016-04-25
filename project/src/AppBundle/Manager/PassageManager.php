<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Document\Passage;

class PassageManager {

    const STATUT_EN_ATTENTE = "EN_ATTENTE";
    const STATUT_A_PLANIFIER = "A_PLANIFIER";
    const STATUT_PLANIFIE = "PLANIFIE";
    const STATUT_REALISE = "REALISE";
    const STATUT_ANNULE = "ANNULE";

    public static $statutsLibellesActions = array(self::STATUT_A_PLANIFIER => 'A planifier',
        self::STATUT_EN_ATTENTE => 'Prévu',
        self::STATUT_PLANIFIE => 'Planifié',
        self::STATUT_REALISE => 'Réalisé',self::STATUT_ANNULE => 'Annulé');
    public static $statutsLibelles = array(self::STATUT_A_PLANIFIER => 'À planifier',
        self::STATUT_EN_ATTENTE => 'En attente',
        self::STATUT_PLANIFIE => 'Planifié',
        self::STATUT_REALISE => 'Réalisé',self::STATUT_ANNULE => 'Annulé');
    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function create(Etablissement $etablissement, Contrat $contrat) {
        $passage = new Passage();

        $passage->setEtablissement($etablissement);
        $passage->setContrat($contrat);
      
        return $passage;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Passage');
    }


    
     public function getNextPassageFromPassage($passage) {
        $contrat = $passage->getContrat();
        if(!$contrat){
            throw new Exception(sprintf("Le contrat du passage %s n'a pas été renseigné! ",$passage->getId()));
            return null;
        }
        $etablissement = $passage->getEtablissement();
        $passagesEtablissement = $contrat->getPassagesEtablissementNode($etablissement);
        $nextPassage = null;
        $founded = false;
        foreach($passagesEtablissement->getPassagesSorted() as $key => $passageEtb){
            $nextPassage = $passageEtb;
            if($founded){
                break;
            }
            if($key == $passage->getId()){
                $founded = true;
            }           
        }
        return $nextPassage;
    }

    public function isFirstPassageNonRealise($passage) {
        $contrat = $passage->getContrat();
        if(!$contrat){
            throw new Exception(sprintf("Le contrat du passage %s n'a pas été renseigné! ",$passage->getId()));
            return null;
        }
        $etablissement = $passage->getEtablissement();
        $passagesEtablissement = $contrat->getPassagesEtablissementNode($etablissement);
        $passagePrecedent = null;
        foreach($passagesEtablissement->getPassagesSorted() as $key => $passageEtb){
            if(($passage->getId() == $passageEtb->getId()) && (is_null($passagePrecedent) || $passagePrecedent->isRealise())){
                return true;
            }  
            $passagePrecedent = $passageEtb;
        }
        return false;
    }
    
}

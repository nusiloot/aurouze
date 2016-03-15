<?php
namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Contrat;
use AppBundle\Document\Etablissement;

class ContratManager {
    
    protected $dm;

    function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    function create(Etablissement $etablissement) 
    {
        $contrat = new Contrat();
        $contrat->setEtablissement($etablissement);  
        $contrat->setIdentifiant($this->getNextNumero($etablissement));       
        $contrat->generateId();
        $contrat->setStatut(Contrat::STATUT_BROUILLON);
        return $contrat;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Contrat');
    }

    public function getNextNumero($etablissement) 
    {
    	$next = $this->getRepository()->findNextNumero($etablissement);
		return $etablissement->getIdentifiant().sprintf("%06d", $next);
    }
    
   
    
}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RechercheController extends Controller {

	/**
	 * @Route("/recherche", name="recherche")
	 */
	public function indexAction(Request $request) 
	{
		$dm = $this->get('doctrine_mongodb')->getManager();
        $query = $request->get('q');
        
        $result = array_merge($dm->getRepository('AppBundle:Societe')->findByQuery($query), $dm->getRepository('AppBundle:Etablissement')->findByQuery($query));
        $result = array_merge($result, $dm->getRepository('AppBundle:Compte')->findByQuery($query));
        
        usort($result, array("AppBundle\Controller\RechercheController", "cmpContacts"));
        
		return $this->render('recherche/index.html.twig', array('query' => $query, 'result' => $result));
	}

	/**
	 * @Route("/recherche/societe", name="recherche_societe")
	 */
	public function societeAction(Request $request) 
	{
		$dm = $this->get('doctrine_mongodb')->getManager();
        $query = $request->get('q');
        $inactif = $request->get('inactif', false);
        $inactif = ($inactif)? true : false;
        
        $result = array_merge($dm->getRepository('AppBundle:Societe')->findByQuery($query, $inactif), $dm->getRepository('AppBundle:Etablissement')->findByQuery($query, $inactif));
        $result = array_merge($result, $dm->getRepository('AppBundle:Compte')->findByQuery($query, $inactif));
        usort($result, array("AppBundle\Controller\RechercheController", "cmpContacts"));
        

        $result = $this->contructSearchResult($result);
        
        $response = new Response(); 
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
	}
	
	public static function cmpContacts($a, $b) 
	{
		return ($a['score'] > $b['score']) ? -1 : +1;
	}
	


    public function contructSearchResult($items) 
    {
		$result = array();
        foreach ($items as $item) {
        	$object = $item['doc'];
            $newResult = new \stdClass();
            $newResult->id = ($item['instance'] == 'Societe')? $object->getId() : $object->getSociete()->getId();
            $newResult->identifiant = $object->getIdentifiant();
            $newResult->icon = $object->getIcon();
            $newResult->libelle = $object->getLibelleComplet();
            $newResult->instance = $item['instance'];
            $newResult->actif = ($object->getActif())? 1 : 0;
            $result[] = $newResult;
        }
        return $result;
    }
	
	
}

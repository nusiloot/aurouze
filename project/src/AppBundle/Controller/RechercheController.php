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
	
	public static function cmpContacts($a, $b) 
	{
		return ($a['score'] > $b['score']) ? -1 : +1;
	}
	
	
}

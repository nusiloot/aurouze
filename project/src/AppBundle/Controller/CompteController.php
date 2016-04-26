<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\TechnicienChoiceType as TechnicienChoiceType;
use AppBundle\Document\Compte;

class CompteController extends Controller {

    /**
     * @Route("/comptes", name="comptes")
     */
    public function comptesAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $comptes = $dm->getRepository('AppBundle:Compte')->findAll();
        return $this->render('compte/listing.html.twig', array('comptes' => $comptes));
    }
    
    /**
     * @Route("/compte/{id}/etat", name="compte_update_etat")
     * @ParamConverter("compte", class="AppBundle:Compte")
     */
    public function updateEtatAction(Request $request, User $compte)
    {
    	if (!$request->isXmlHttpRequest()) {
    		throw $this->createNotFoundException();
    	}

    	$dm = $this->get('doctrine_mongodb')->getManager();
    	if ($compte) {
    		try {
	    		$compte->setActif($request->get('etat'));
	    		$dm->persist($compte);
	    		$dm->flush();
	    		return new Response(json_encode(array("success" => true)));
    		} catch (\Exception $e) { }
    	}
    	
    	throw new \Exception('Une erreur s\'est produite');
    }

}

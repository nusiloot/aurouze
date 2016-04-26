<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\TechnicienChoiceType as TechnicienChoiceType;
use AppBundle\Document\User;

class UserController extends Controller {

    /**
     * @Route("/users", name="users")
     */
    public function usersAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $users = $dm->getRepository('AppBundle:User')->findAll();
        return $this->render('user/listing.html.twig', array('users' => $users));
    }
    
    /**
     * @Route("/user/{id}/etat", name="user_update_etat")
     * @ParamConverter("user", class="AppBundle:User")
     */
    public function updateEtatAction(Request $request, User $user)
    {
    	if (!$request->isXmlHttpRequest()) {
    		throw $this->createNotFoundException();
    	}

    	$dm = $this->get('doctrine_mongodb')->getManager();
    	if ($user) {
    		try {
	    		$user->setActif($request->get('etat'));
	    		$dm->persist($user);
	    		$dm->flush();
	    		return new Response(json_encode(array("success" => true)));
    		} catch (\Exception $e) { }
    	}
    	
    	throw new \Exception('Une erreur s\'est produite');
    }

}

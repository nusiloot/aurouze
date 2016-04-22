<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\SocieteChoiceType;
use AppBundle\Type\SocieteType;
use AppBundle\Document\Societe;

class SocieteController extends Controller {

    /**
     * @Route("/societes", name="societe")
     */
    public function indexAction() {
    	
    	$dm = $this->get('doctrine_mongodb')->getManager();
    	$form = $this->createForm(SocieteChoiceType::class, null, array(
    			'action' => $this->generateUrl('societe_choice'),
    			'method' => 'POST',
    	));

    	return $this->render('societe/index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/societes/selection", name="societe_choice")
     */
    public function societeChoiceAction(Request $request) {
    	$formData = $request->get('societe_choice');
    
    	return $this->redirectToRoute('societe_visualisation', array('id' => $formData['societes']));
    }

    /**
     * @Route("/societe/{id}/visualisation", name="societe_visualisation")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function visualisationAction(Request $request, $societe) {

    	return $this->render('societe/visualisation.html.twig', array('societe' => $societe));
    }
    
    /**
     * @Route("/societes/modification/{id}", defaults={"id" = null}, name="societe_modification")
     */
    public function modificationAction(Request $request, $id) {
    	
    	$dm = $this->get('doctrine_mongodb')->getManager();
    	
    	$societe = ($id)? $this->get('societe.manager')->getRepository()->find($id) : new Societe();
    	
    	$form = $this->createForm(new SocieteType($this->container, $dm), $societe, array(
    			'action' => $this->generateUrl('societe_modification', array('id' => $id)),
    			'method' => 'POST',
    	));
    	$form->handleRequest($request);
    	if ($form->isSubmitted() && $form->isValid()) {
    		$societe = $form->getData();
    		$dm->persist($societe);
    		$dm->flush();
    		return $this->redirectToRoute('societe_visualisation', array('id' => $societe->getId()));
    	}

    	return $this->render('societe/modification.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/societes/rechercher", name="societe_search")
     */
    public function societeSearchAction(Request $request) {

        $term = $request->get('term');
        $response = new Response();
        $result = array();
        if (strlen($term) >= 3) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $byNom = $dm->getRepository('AppBundle:Societe')->findByTerm($term, 'raisonSociale');
            $byAdresse = $dm->getRepository('AppBundle:Societe')->findByTerm($term, 'adresse.adresse');
            $byCp = $dm->getRepository('AppBundle:Societe')->findByTerm($term, 'adresse.code_postal');
            $byCommune = $dm->getRepository('AppBundle:Societe')->findByTerm($term, 'adresse.commune');
            $this->contructSearchResult($byNom, $result);
            $this->contructSearchResult($byAdresse, $result);
            $this->contructSearchResult($byCp, $result);
            $this->contructSearchResult($byCommune, $result);
        }
        $data = json_encode($result);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($data);
        return $response;
    }

    public function contructSearchResult($criterias, &$result) {

        foreach ($criterias as $criteria) {
            $newResult = new \stdClass();
            $newResult->id = $criteria->getId();
            $newResult->term = $criteria->getRaisonSociale();
            $result[] = $newResult;
        }
    }

}
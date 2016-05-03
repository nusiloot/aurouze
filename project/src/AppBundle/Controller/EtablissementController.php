<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Manager\PassageManager;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Type\EtablissementChoiceType;
use AppBundle\Type\EtablissementType;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Societe;


class EtablissementController extends Controller {

    /**
     * @Route("/etablissement-choix", name="etablissement_choice")
     */
    public function choiceAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $form = $this->createForm(new EtablissementChoiceType(), array(
            'action' => $this->generateUrl('etablissement_choice'),
            'method' => 'POST',
        ));

        return $this->render('default/etablissementChoixForm.html.twig', array('etablissements' => $etablissements));
    }

    /**
     * @Route("/etablissement-search", name="etablissement_search")
     */
    public function searchAction(Request $request) {

        $term = $request->get('term');
        $response = new Response();
        $etablissementsResult = array();
        if (strlen($term) >= 3) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $etablissementsByIdentifiant = $dm->getRepository('AppBundle:Etablissement')->findByIdentifiant($term, 'identifiant');
            $etablissementsByNom = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'nom');
            $etablissementsByAdresse = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'adresse.adresse');
            $etablissementsByCp = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'adresse.code_postal');
            $etablissementsByCommune = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'adresse.commune');
            $this->contructSearchResult($etablissementsByIdentifiant, $etablissementsResult);
            $this->contructSearchResult($etablissementsByNom, $etablissementsResult);
            $this->contructSearchResult($etablissementsByAdresse, $etablissementsResult);
            $this->contructSearchResult($etablissementsByCp, $etablissementsResult);
            $this->contructSearchResult($etablissementsByCommune, $etablissementsResult);
        }
        $data = json_encode($etablissementsResult);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($data);
        return $response;
    }

    /**
     * @Route("/societe-search", name="societe_search")
     */
    public function societeSearchAction(Request $request) {
        $term = $request->get('term');
        $response = new Response();
        $societesResult = array();
        if (strlen($term) >= 3) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $societesByNom = $dm->getRepository('AppBundle:Societe')->findByTerm($term, 'nom');
            $societesByAdresse = $dm->getRepository('AppBundle:Societe')->findByTerm($term, 'adresse.adresse');
            $societesByCp = $dm->getRepository('AppBundle:Societe')->findByTerm($term, 'adresse.code_postal');
            $societesByCommune = $dm->getRepository('AppBundle:Societe')->findByTerm($term, 'adresse.commune');
            $this->societeContructSearchResult($societesByNom, $societesResult);
            $this->societeContructSearchResult($societesByAdresse, $societesResult);
            $this->societeContructSearchResult($societesByCp, $societesResult);
            $this->societeContructSearchResult($societesByCommune, $societesResult);
        }
        $data = json_encode($societesResult);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($data);
        return $response;
    }

    public function societeContructSearchResult($societesByCriteria, &$societesResult) {

        foreach ($societesByCriteria as $societe) {
            $newResult = new \stdClass();
            $newResult->id = $societe->getId();
            $newResult->term = $societe->getIntitule();
            $societesResult[] = $newResult;
        }
    }

    public function contructSearchResult($etablissementsByCriteria, &$etablissementsResult) {

        foreach ($etablissementsByCriteria as $etablissement) {
            $newResult = new \stdClass();
            $newResult->id = $etablissement->getId();
            $newResult->term = $etablissement->getIntitule();
            $etablissementsResult[] = $newResult;
        }
    }
    
    /**
     * @Route("/etablissement/{societe}/modification/{id}", defaults={"id" = null}, name="etablissement_modification")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function modificationAction(Request $request, $societe, $id) {
    	
    	$dm = $this->get('doctrine_mongodb')->getManager();
    	$etablissement = ($id)? $this->get('etablissement.manager')->getRepository()->find($id) : new Etablissement();
    	
    	$etablissement->setSociete($societe);
    	
    	$form = $this->createForm(new EtablissementType($this->container, $dm), $etablissement, array(
    			'action' => $this->generateUrl('etablissement_modification', array('societe' => $societe->getId(), 'id' => $id)),
    			'method' => 'POST',
    	));
    	$form->handleRequest($request);
    	if ($form->isSubmitted() && $form->isValid()) {
    		$etablissement = $form->getData();
    		$dm->persist($etablissement); 	
    		$dm->flush();
    		return $this->redirectToRoute('societe_visualisation', array('id' => $societe->getId()));
    	}

    	return $this->render('etablissement/modification.html.twig', array('societe' => $societe, 'form' => $form->createView(), 'etablissement' => $etablissement));
    }

}

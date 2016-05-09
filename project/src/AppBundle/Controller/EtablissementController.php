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
            'method' => 'GET',
        ));

        return $this->render('default/etablissementChoixForm.html.twig', array('etablissements' => $etablissements));
    }

    /**
     * @Route("/etablissement-search", name="etablissement_search")
     */
    public function searchAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $response = new Response();
        $etablissementsResult = array();

        $this->contructSearchResult($dm->getRepository('AppBundle:Etablissement')->findByTerms($request->get('term')), $etablissementsResult);

        $data = json_encode($etablissementsResult);
        $response->headers->set('Content-Type', 'text/json');
        $response->setContent($data);
        return $response;
    }

    public function contructSearchResult($etablissementsByCriteria, &$etablissementsResult) {

        foreach ($etablissementsByCriteria as $id => $nom) {
            $newResult = new \stdClass();
            $newResult->id = $id;
            $newResult->text = $nom;
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

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Type\ContratType;

class ContratController extends Controller {
	
	/**
	 * @Route("/contrat/{identifiantEtablissement}/creation", name="contrat_creation")
	 */
	public function creationAction(Request $request, $identifiantEtablissement) {
		$dm = $this->get('doctrine_mongodb')->getManager();
		$etablissement = $dm->getRepository('AppBundle:Etablissement')->findOneByIdentifiant($identifiantEtablissement);
		$contrat = $this->get('contrat.manager')->create($etablissement);
        $dm->persist($contrat);
        $dm->flush();
		return $this->redirectToRoute('contrat_modification', array('identifiantContrat' => $contrat->getIdentifiant()));
	}

    /**
     * @Route("/contrat/{identifiantContrat}/modification", name="contrat_modification")
     */
    public function modificationAction(Request $request, $identifiantContrat) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneByIdentifiant($identifiantContrat);
        $form = $this->createForm(new ContratType($this->container, $dm), $contrat, array(
          'action' => $this->generateUrl('contrat_modification', array('identifiantContrat' => $identifiantContrat)),
          'method' => 'POST',
          ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        	$contrat = $form->getData();
//                $nextPassage = $contrat->getNextPassage();
//                $dm->persist($nextPassage);
//                $contrat->addPassage($nextPassage);
        	$dm->persist($contrat);
        	$dm->flush();
        	return $this->redirectToRoute('contrat_validation', array('identifiantContrat' => $contrat->getIdentifiant()));
        }
        return $this->render('contrat/modification.html.twig', array('contrat' => $contrat, 'form' => $form->createView()));
    }
    
    /**
     * @Route("/contrat/{identifiantContrat}/validation", name="contrat_validation")
     */
    public function validationAction(Request $request, $identifiantContrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneByIdentifiant($identifiantContrat);
        if ($request->get('valide')) {
        	$contrat->setStatut(Contrat::STATUT_VALIDE);
        	$dm->persist($contrat);
        	$dm->flush();
        	return $this->redirectToRoute('contrat_visualisation', array('identifiantContrat' => $contrat->getIdentifiant()));
        }
    	return $this->render('contrat/validation.html.twig', array('contrat' => $contrat));
    }
    
    /**
     * @Route("/contrat/{identifiantContrat}/visualisation", name="contrat_visualisation")
     */
    public function visualisationAction(Request $request, $identifiantContrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneByIdentifiant($identifiantContrat);
    	return $this->render('contrat/visualisation.html.twig', array('contrat' => $contrat));
    }
    
    /**
     * @Route("/contrat/{identifiantContrat}/suppression", name="contrat_suppression")
     */
    public function suppressionAction(Request $request, $identifiantContrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneByIdentifiant($identifiantContrat);
        $etablissementIdentifiant = $contrat->getEtablissement()->getIdentifiant();
        $dm->remove($contrat);
        $dm->flush();
        return $this->redirectToRoute('passage_etablissement', array('identifiantEtablissement' => $etablissementIdentifiant));
    }

}

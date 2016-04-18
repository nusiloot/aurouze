<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Configuration;
use AppBundle\Type\ConfigurationPrestationsType;
use AppBundle\Type\ConfigurationProduitsType;
use AppBundle\Type\ConfigurationProvenancesType;

class ConfigurationController extends Controller {

    /**
     * @Route("/configuration", name="configuration")
     */
    public function configurationAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $configuration = $dm->getRepository('AppBundle:Configuration')->findConfiguration();
        if(!$configuration){
           $configuration = new Configuration();
           $configuration->setId(Configuration::PREFIX); 
        }
        return $this->render('configuration/visualisation.html.twig', array('configuration' => $configuration));
    }
    
    /**
     * @Route("/configuration-modification-produits", name="configuration_modification_produits")
     */
    public function modificationProduitsAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $configuration = $dm->getRepository('AppBundle:Configuration')->findConfiguration();
        if(!$configuration){
            $configuration = new Configuration();
            $configuration->setId(Configuration::PREFIX); 
            $dm->persist($configuration);
            $dm->flush();
        }
             
        
        $form = $this->createForm(new ConfigurationProduitsType($this->container, $dm), $configuration, array(
            'action' => $this->generateUrl('configuration_modification_produits'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $configuration = $form->getData();
            $dm->persist($configuration);
            $dm->flush();
            return $this->redirectToRoute('configuration');
        }
        return $this->render('configuration/modificationProduits.html.twig', array('configuration' => $configuration,'form' => $form->createView()));
    }
    
    
    /**
     * @Route("/configuration-modification-prestations", name="configuration_modification_prestations")
     */
    public function modificationPrestationsAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $configuration = $dm->getRepository('AppBundle:Configuration')->findConfiguration();
        if(!$configuration){
            $configuration = new Configuration();
            $configuration->setId("CONFIGURATION"); 
            $dm->persist($configuration);
            $dm->flush();
        }
        $form = $this->createForm(new ConfigurationPrestationsType($this->container, $dm), $configuration, array(
            'action' => $this->generateUrl('configuration_modification_prestations'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $configuration = $form->getData();
            $dm->persist($configuration);
            $dm->flush();
            return $this->redirectToRoute('configuration');
        }
        
      
        return $this->render('configuration/modificationPrestations.html.twig', array('configuration' => $configuration,'form' => $form->createView()));
    }
    


    /**
     * @Route("/configuration-modification-provenances", name="configuration_modification_provenances")
     */
    public function modificationProvenancesAction(Request $request) {
    	$dm = $this->get('doctrine_mongodb')->getManager();
    
    	$configuration = $dm->getRepository('AppBundle:Configuration')->findConfiguration();
    	if(!$configuration){
    		$configuration = new Configuration();
    		$configuration->setId(Configuration::PREFIX);
    		$dm->persist($configuration);
    		$dm->flush();
    	}
    	 
    
    	$form = $this->createForm(new ConfigurationProvenancesType($this->container, $dm), $configuration, array(
    			'action' => $this->generateUrl('configuration_modification_provenances'),
    			'method' => 'POST',
    	));
    	$form->handleRequest($request);
    	if ($form->isSubmitted() && $form->isValid()) {
    		$configuration = $form->getData();
    		$dm->persist($configuration);
    		$dm->flush();
    		return $this->redirectToRoute('configuration');
    	}
    	return $this->render('configuration/modificationProvenances.html.twig', array('configuration' => $configuration,'form' => $form->createView()));
    }

}

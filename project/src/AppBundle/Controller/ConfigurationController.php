<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Configuration;
use AppBundle\Type\ConfigurationType;

class ConfigurationController extends Controller {

    /**
     * @Route("/configuration", name="configuration")
     */
    public function configurationAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $configuration = $dm->getRepository('AppBundle:Configuration')->findConfiguration();
        return $this->render('configuration/visualisation.html.twig', array('configuration' => $configuration));
    }
    
    /**
     * @Route("/configuration-modification", name="configuration_modification")
     */
    public function modificationAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $configuration = $dm->getRepository('AppBundle:Configuration')->findConfiguration();
        if(!$configuration){
            $configuration = new Configuration();
            $configuration->setId("CONFIGURATION"); 
            $dm->persist($configuration);
            $dm->flush();
        }
        
        $form = $this->createForm(new ConfigurationType($this->container, $dm), $configuration, array(
            'action' => $this->generateUrl('configuration_modification'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $configuration = $form->getData();
            $dm->persist($configuration);
            $dm->flush();
            return $this->redirectToRoute('configuration');
        }
        return $this->render('configuration/modification.html.twig', array('configuration' => $configuration, 'form' => $form->createView()));
    }

}

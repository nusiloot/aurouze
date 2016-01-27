<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Manager\PassageManager as PassageManager;
use AppBundle\Manager\EtablissementManager as EtablissementManager;

class DefaultController extends Controller {

    /**
     * @Route("/", name="accueil")
     */
    public function indexAction(Request $request) {

        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/passage/{identifiantEtablissement}", name="passageEtablissement")
     */
    public function passageEtablissementAction(Request $request) {
       
        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissementManager = new EtablissementManager($dm);
        $etablissement = $etablissementManager->create();
        $dm->persist($etablissement);
        $dm->flush();

        return new Response('Created product id ' . $etablissement->getId());

        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }
    
    /**
     * @Route("/creation-passage/{identifiantEtablissement}", name="passageCreate")
     */
    public function passageCreateAction(Request $request) {
       
        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissement = $dm->getRepository('AppBundle:Etablissement')->findOneByIdentifiant($request->get('identifiantEtablissement'));
        
        $passageManager = new PassageManager($dm);
        $passage = $passageManager->create($etablissement);
        $dm->persist($passage);
        $dm->flush();

        return new Response('Created passage id ' . $passage->getId());

        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }
    
     /**
     * @Route("/creation-etablissement", name="etablissementCreate")
     */
    public function etablissementCreateAction(Request $request) {
       
        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissementManager = new EtablissementManager($dm);
        $etablissement = $etablissementManager->create();
        $dm->persist($etablissement);
        $dm->flush();

        return new Response('Created product id ' . $etablissement->getId());

        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }

}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Passage as Passages;
use AppBundle\Manager\EtablissementManager as EtablissementManager;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
        $etablissement = new \AppBundle\Document\Etablissement();


        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissementManager = new EtablissementManager($dm);
        $etablissementManager->create();
        exit;
        $dm->persist($etablissement);
        $dm->flush();

        return new Response('Created product id ' . $etablissement->getId());

        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }

}

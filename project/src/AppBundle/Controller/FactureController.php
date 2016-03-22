<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Facture;
use AppBundle\Document\FactureLigne;
use AppBundle\Type\FactureType;

class FactureController extends Controller {

    /**
     * @Route("/facture", name="facture")
     */
    public function indexAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $facture = new Facture();
        $facture->setId('FACTURE');
        $facture->addLigne(new FactureLigne());

        $form = $this->createForm(new FactureType(), $facture, array(
            'action' => $this->generateUrl('facture'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            return $this->render('facture/index.html.twig', array('form' => $form->createView()));
        }

        $dm->persist($facture);
        $dm->flush();

        return $this->redirectTo('facture/index.html.twig', array('form' => $form->createView()));
    }

}

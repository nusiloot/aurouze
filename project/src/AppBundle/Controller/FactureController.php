<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Facture;
use AppBundle\Type\FactureType;

class FactureController extends Controller {

    /**
     * @Route("/facture", name="facture")
     */
    public function indexAction() {

        $facture = new Facture();

        $form = $this->createForm(new FactureType(), $facture, array(
            'action' => $this->generateUrl('facture'),
            'method' => 'POST',
        ));

        return $this->render('facture/index.html.twig', array('form' => $form->createView()));
    }

}

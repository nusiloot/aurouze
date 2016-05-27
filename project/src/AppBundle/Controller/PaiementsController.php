<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Type\PaiementsType;
use AppBundle\Document\Paiements;

class PaiementsController extends Controller {

    /**
     * @Route("/paiements/liste", name="paiements_liste")
     */
    public function indexAction(Request $request) {

        $paiementsDocs = $this->get('paiements.manager')->getRepository()->getLastPaiements(10);

        return $this->render('paiements/index.html.twig', array('paiementsDocs' => $paiementsDocs));
    }

    /**
     * @Route("/paiements/{id}/modification", name="paiements_modification")
     * @ParamConverter("paiements", class="AppBundle:Paiements")
     */
    public function modificationAction(Request $request, $paiements) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $form = $this->createForm(new PaiementsType($this->container, $dm), $paiements, array(
            'action' => $this->generateUrl('paiements_modification', array('id' => $paiements->getId())),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paiements = $form->getData();

            $dm->persist($paiements);
            $dm->flush();
            return $this->redirectToRoute('paiements_modification', array('id' => $paiements->getId()));
        }

        return $this->render('paiements/modification.html.twig', array('paiements' => $paiements, 'form' => $form->createView()));
    }

    /**
     * @Route("/paiements/nouveau", name="paiements_nouveau")
     */
    public function nouveauAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $paiements = new Paiements();

        $form = $this->createForm(new PaiementsType($this->container, $dm), $paiements, array(
            'action' => $this->generateUrl('paiements_nouveau'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paiements = $form->getData();

            $dm->persist($paiements);
            $dm->flush();
            return $this->redirectToRoute('paiements_modification', array('id' => $paiements->getId()));
        }

        return $this->render('paiements/nouveau.html.twig', array('form' => $form->createView()));
    }

}

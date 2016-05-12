<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Facture;
use AppBundle\Document\FactureLigne;
use AppBundle\Type\FactureType;
use AppBundle\Document\Societe;
use AppBundle\Type\SocieteChoiceType;

/**
 * Facture controller.
 *
 * @Route("/facture")
 */
class FactureController extends Controller {

    /**
     * @Route("/", name="facture")
     */
    public function indexAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $formSociete = $this->createForm(SocieteChoiceType::class, array(), array(
            'action' => $this->generateUrl('facture_societe_choice'),
            'method' => 'POST',
        ));

        return $this->render('facture/index.html.twig', array('formSociete' => $formSociete->createView()));
    }

    public function editionAction(Request $request) {
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

    /**
     * @Route("/etablissement-choix", name="facture_societe_choice")
     */
    public function societeChoiceAction(Request $request) {
        $formData = $request->get('societe_choice');

        return $this->redirectToRoute('facture_societe', array('id' => $formData['societes']));
    }

    /**
     * @Route("/societe/{id}", name="facture_societe")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function societeAction(Request $request, Societe $societe) {
        $fm = $this->get('facture.manager');

        $formSociete = $this->createForm(SocieteChoiceType::class, array('societes' => $societe->getIdentifiant(), 'societe' => $societe), array(
            'action' => $this->generateUrl('facture_societe_choice'),
            'method' => 'POST',
        ));
        $factures = $fm->findBySociete($societe);
        $mouvements = $fm->getMouvementsBySociete($societe);

        return $this->render('facture/societe.html.twig', array('societe' => $societe, 'mouvements' => $mouvements, 'formSociete' => $formSociete->createView(), 'factures' => $factures));
    }

    /**
     * @Route("/societe/{id}/generation", name="facture_societe_generation")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function societeGenerationAction(Request $request, Societe $societe) {
        $fm = $this->get('facture.manager');
        $dm = $this->get('doctrine_mongodb')->getManager();
        $date = new \DateTime($request->get('dateFacturation', date('d/m/Y')));

        $mouvements = $fm->getMouvementsBySociete($societe);

        $facture = $fm->create($societe, $mouvements, new \DateTime());
        $facture->setDateFacturation($date);
        $dm->persist($facture);
        $dm->flush();

        return $this->redirectToRoute('facture_societe', array('id' => $societe->getId()));
    }

    /**
     * @Route("/pdf/{id}", name="facture_pdf")
     * @ParamConverter("etablissement", class="AppBundle:Facture")
     */
    public function pdfAction(Request $request, Facture $facture) {
        $fm = $this->get('facture.manager');

        $html = $this->renderView('facture/pdf.html.twig', array(
                'facture' => $facture,
                'parameters' => $fm->getParameters(),
            ));

        if($request->get('output') == 'html') {

            return new Response($html, 200);
        }

        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
    					'Content-Disposition'   => 'attachment; filename="facture-'.$facture->getNumeroFacture().'.pdf"'
                )
        );
    }

}

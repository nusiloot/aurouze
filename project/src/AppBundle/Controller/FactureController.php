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
use AppBundle\Document\Etablissement;
use AppBundle\Type\EtablissementChoiceType as EtablissementChoiceType;

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
     * @Route("/etablissement-choix", name="facture_etablissement_choice")
     */
    public function etablissementChoiceAction(Request $request) {
        $formData = $request->get('etablissement_choice');

        return $this->redirectToRoute('facture_etablissement', array('id' => $formData['etablissements']));
    }

    /**
     * @Route("/etablissement/{id}", name="facture_etablissement")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function etablissementAction(Request $request, Etablissement $etablissement) {
        $fm = $this->get('facture.manager');

        $formEtablissement = $this->createForm(EtablissementChoiceType::class, array('etablissements' => $etablissement->getIdentifiant(), 'etablissement' => $etablissement), array(
            'action' => $this->generateUrl('facture_etablissement_choice'),
            'method' => 'POST',
        ));
        $factures = $fm->findByEtablissement($etablissement);
        $mouvements = $fm->getMouvementsByEtablissement($etablissement);

        return $this->render('facture/etablissement.html.twig', array('etablissement' => $etablissement, 'mouvements' => $mouvements, 'formEtablissement' => $formEtablissement->createView(), 'factures' => $factures));
    }

    /**
     * @Route("/etablissement/{id}/generation", name="facture_etablissement_generation")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function etablissementGenerationAction(Request $request, Etablissement $etablissement) {
        $fm = $this->get('facture.manager');
        $dm = $this->get('doctrine_mongodb')->getManager();

        $mouvements = $fm->getMouvementsByEtablissement($etablissement);

        $facture = $fm->create($etablissement, $mouvements);
        $dm->persist($facture);
        $dm->flush();

        return $this->redirectToRoute('facture_etablissement', array('id' => $etablissement->getId()));
    }

    /**
     * @Route("/pdf/{id}", name="facture_pdf")
     * @ParamConverter("etablissement", class="AppBundle:Facture")
     */
    public function pdfAction(Request $request, Facture $facture) {

        $html = $this->renderView('facture/pdf.html.twig', array(
                'facture' => $facture
            ));

        if($request->get('output') == 'html') {

            return new Response($html, 200);
        }

        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="file.pdf"'
                )
        );
    }

}

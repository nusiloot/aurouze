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

    /**
     * @Route("/societe/{societe}/creation/{type}", name="facture_creation")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function creationAction(Request $request, Societe $societe, $type) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $cm = $this->get('configuration.manager');
        $fm = $this->get('facture.manager');

        if($request->get('id')) {
            $facture = $fm->getRepository()->findOneById($request->get('id'));
        }

        if($request->get('id') && !$facture) {

            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf("La facture %s n'a pas été trouvé", $request->get('id')));
        }

        if(!isset($facture)) {
            $facture = $fm->createVierge($societe);
            $factureLigne = new FactureLigne();
            $factureLigne->setTauxTaxe(0.2);
            $facture->addLigne($factureLigne);
        }

        $facture->setSociete($societe);
        $facture->setDateEmission(new \DateTime());

        if($type == "devis" && !$facture->getDateDevis()) {
            $facture->setDateDevis(new \DateTime());
        } elseif(!$facture->getDateFacturation()) {
            $facture->setDateFacturation(new \DateTime());
        }

        $produitsSuggestion = array();
        foreach($cm->getConfiguration()->getProduits() as $produit) {
            $produitsSuggestion[] = array("libelle" => $produit->getNom(), "conditionnement" => $produit->getConditionnement(), "identifiant" => $produit->getIdentifiant(), "prix" => $produit->getPrixVente());
        }

        $form = $this->createForm(new FactureType($dm, $cm, $facture->isDevis()), $facture, array(
            'action' => "",
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            return $this->render('facture/libre.html.twig', array('form' => $form->createView(), 'produitsSuggestion' => $produitsSuggestion, 'societe' => $societe, 'facture' => $facture));
        }

        $facture->update();

        if($request->get('previsualiser')) {

            return $this->pdfAction($request, $facture);
        }

        $dm->persist($facture);
        $dm->flush();

        return $this->redirectToRoute('facture_societe', array('id' => $societe->getId()));
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
        $date = \DateTime::createFromFormat('d/m/Y', $request->get('dateFacturation', date('d/m/Y')));

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
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $this->getPdfGenerationOptions()),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
    					'Content-Disposition'   => 'attachment; filename="facture-'.$facture->getNumeroFacture().'.pdf"'
                )
        );
    }

    public function getPdfGenerationOptions() {
        return array('disable-smart-shrinking' => null, 'encoding' => 'utf-8', 'margin-left' => 3, 'margin-right' => 3, 'margin-top' => 4, 'margin-bottom' => 4);
    }

    /**
     * @Route("/facture/rechercher", name="facture_search")
     */
     public function factureSearchAction(Request $request) {
         $dm = $this->get('doctrine_mongodb')->getManager();
         $response = new Response();
         $facturesResult = array();
         $this->contructSearchResult($dm->getRepository('AppBundle:Facture')->findByTerms($request->get('term')),  $facturesResult);
         $data = json_encode($facturesResult);
         $response->headers->set('Content-Type', 'application/json');
         $response->setContent($data);
         return $response;
     }

    public function contructSearchResult($criterias, &$result) {

        foreach ($criterias as $id => $nom) {
            $newResult = new \stdClass();
            $newResult->id = $id;
            $newResult->text = $nom;
            $result[] = $newResult;
        }
    }

}

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
use AppBundle\Document\Contrat;
use AppBundle\Document\Societe;
use AppBundle\Type\FactureChoiceType;
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
    	$form = $this->createForm(FactureChoiceType::class, null, array(
    			'action' => $this->generateUrl('facture'),
    			'method' => 'GET',
    	));
    	$result = array();
    	$search = false;
    	$query = false;
    	$form->handleRequest($request);
    	if ($form->isSubmitted() && $form->isValid()) {
    		$query = $form->getData()['factures'];
    		$search = is_null($query) ? false : true;
    		$result = $dm->getRepository('AppBundle:Facture')->findByQuery($query);
    		usort($result, array("AppBundle\Controller\RechercheController", "cmpContacts"));
    	}

        return $this->render('facture/index.html.twig', array('form' => $form->createView(), 'result' => $result, 'search' => $search, 'query' => $query));
    }

    /**
     * @Route("/societe/{societe}/creation/{type}", name="facture_creation")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function creationAction(Request $request, Societe $societe, $type) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $cm = $this->get('configuration.manager');
        $fm = $this->get('facture.manager');

        if ($request->get('id')) {
            $facture = $fm->getRepository()->findOneById($request->get('id'));
        }

        if ($request->get('id') && !$facture) {

            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf("La facture %s n'a pas été trouvé", $request->get('id')));
        }

        if (!isset($facture)) {
            $facture = $fm->createVierge($societe);
            $factureLigne = new FactureLigne();
            $factureLigne->setTauxTaxe(0.2);
            $facture->addLigne($factureLigne);
        }

        $facture->setSociete($societe);

        if(!$facture->getId()) {
            $facture->setDateEmission(new \DateTime());
        }

        if ($type == "devis" && !$facture->getDateDevis()) {
            $facture->setDateDevis(new \DateTime());
        } elseif ($type == "facture" && !$facture->getDateFacturation()) {
            $facture->setDateFacturation(new \DateTime());
        }

        $produitsSuggestion = array();
        foreach ($cm->getConfiguration()->getProduits() as $produit) {
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

        if ($request->get('previsualiser')) {

            return $this->pdfAction($request, $facture);
        }

        if (!$facture->getId()) {
            $dm->persist($facture);
        } elseif ($facture->isFacture() && !$facture->getNumeroFacture()) {
            $fm->getRepository()->getClassMetadata()->idGenerator->generateNumeroFacture($dm, $facture);
        }

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

        foreach($mouvements as $mouvement) {
            $facture = $fm->create($societe, array($mouvement), new \DateTime());
            $facture->setDateFacturation($date);
            $dm->persist($facture);
            $dm->flush();
        }

        return $this->redirectToRoute('facture_societe', array('id' => $societe->getId()));
    }

    /**
     * @Route("/cloturer/{id}/{factureId}", name="facture_cloturer")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function cloturerAction(Request $request, Societe $societe, $factureId) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $facture = $this->get('facture.manager')->getRepository()->findOneById($factureId);
        $facture->cloturer();
        $dm->persist($facture);
        $dm->flush();
        return $this->redirectToRoute('facture_societe', array('id' => $societe->getId()));
    }

  /**
     * @Route("/avoir/{id}/{factureId}", name="facture_avoir")
   * @ParamConverter("societe", class="AppBundle:Societe")
   */
  public function avoirAction(Request $request, Societe $societe, $factureId) {
      $dm = $this->get('doctrine_mongodb')->getManager();

      $facture = $this->get('facture.manager')->getRepository()->findOneById($factureId);
      $avoir = $facture->genererAvoir();
      $dm->persist($avoir);
      $dm->flush();

      $facture->setAvoir($avoir->getNumeroFacture());
      $dm->persist($facture);
      $dm->flush();

      $contrat = $facture->getContrat();
      $contrat->restaureMouvements($facture);

      $dm->persist($contrat);
      $dm->flush();

      return $this->redirectToRoute('facture_societe', array('id' => $societe->getId()));
  }


    /**
     * @Route("/decloturer/{id}/{factureId}", name="facture_decloturer")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function decloturerAction(Request $request, Societe $societe, $factureId) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $facture = $this->get('facture.manager')->getRepository()->findOneById($factureId);
        $facture->decloturer();
        $dm->persist($facture);
        $dm->flush();
        return $this->redirectToRoute('facture_societe', array('id' => $societe->getId()));
    }


    /**
     * @Route("/facturer/{id}/{identifiant}", name="facture_defacturer")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function defacturerAction(Request $request, Contrat $contrat, $identifiant) {
        $dm = $this->get('doctrine_mongodb')->getManager();
    	$contrat->resetFacturableMouvement($identifiant);
        $dm->flush();
        return $this->redirectToRoute('facture_societe', array('id' => $contrat->getSociete()->getId()));
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

        if ($request->get('output') == 'html') {

            return new Response($html, 200);
        }

        if ($facture->isDevis() && $facture->getNumeroDevis()) {
            $filename = "devis_" . $facture->getSociete()->getIdentifiant() . "_" . $facture->getDateDevis()->format('Ymd') . "_N" . $facture->getNumeroDevis() . ".pdf";
        } elseif ($facture->isFacture() && $facture->getNumeroFacture()) {
            $prefix = ($facture->isAvoir())? 'avoir' : 'facture';
            $filename = $prefix."_" . $facture->getSociete()->getIdentifiant() . "_" . $facture->getDateFacturation()->format('Ymd') . "_N" . $facture->getNumeroFacture() . ".pdf";
        } elseif ($facture->isDevis()) {
            $filename = "devis_" . $facture->getSociete()->getIdentifiant() . "_" . $facture->getDateDevis()->format('Ymd') . "_brouillon.pdf";
        } else {
            $prefix = ($facture->isAvoir())? 'avoir' : 'facture';
            $filename = $prefix."_" . $facture->getSociete()->getIdentifiant() . "_" . $facture->getDateFacturation()->format('Ymd') . "_brouillon.pdf";
        }

        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $this->getPdfGenerationOptions()), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
        $this->contructSearchResult($dm->getRepository('AppBundle:Facture')->findByTerms($request->get('term')), $facturesResult);
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

    /**
     * @Route("/facture/export", name="facture_export")
     */
    public function exportComptableAction(Request $request) {

      // $response = new StreamedResponse();
        $formRequest = $request->request->get('form');
        $date = \DateTime::createFromFormat('d/m/Y',$formRequest['date']);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $fm = $this->get('facture.manager');
        $facturesForCsv = $fm->getFacturesForCsv($date);

        $filename = sprintf("export_factures_%s.csv", $date->format("Y-m-d"));
        $handle = fopen('php://memory', 'r+');

        foreach ($facturesForCsv as $paiement) {
            fputcsv($handle, $paiement,';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $content = "\xef\xbb\xbf".$content;

        $response = new Response($content, 200, array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ));
        $response->setCharset('UTF-8');

        return $response;
    }

    /**
     * @Route("/stats/export", name="stats_export")
     */
    public function exportStatsAction(Request $request) {

      // $response = new StreamedResponse();
        $formRequest = $request->request->get('form');
        $date = \DateTime::createFromFormat('d/m/Y',$formRequest['date']);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $fm = $this->get('facture.manager');
        $facturesStatsForCsv = $fm->getStatsForCsv($date);



        $filename = sprintf("export_stat_%s.csv", $date->format("Y-m-d"));
        $handle = fopen('php://memory', 'r+');

        foreach ($facturesStatsForCsv as $paiement) {
            fputcsv($handle, $paiement,';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $response = new Response($content, 200, array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ));
        $response->setCharset('UTF-8');

        return $response;
    }


}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Manager\FactureManager;
use AppBundle\Document\Facture;
use AppBundle\Document\FactureLigne;
use AppBundle\Type\FactureType;
use AppBundle\Document\Contrat;
use AppBundle\Document\Societe;
use AppBundle\Type\FactureChoiceType;
use AppBundle\Type\SocieteChoiceType;
use AppBundle\Type\RelanceType;
use AppBundle\Type\FacturesEnRetardFiltresType;

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
        $contratManager = $this->get('contrat.manager');
        $contratsFactureAEditer = $contratManager->getRepository()->findContratWithFactureAFacturer(50);


        return $this->render('facture/index.html.twig',array('contratsFactureAEditer' => $contratsFactureAEditer));
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

        $commercial = $dm->getRepository('AppBundle:Compte')->findOneByIdentifiant('003480005');
        $facture->setCommercial($commercial);
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
     * @Route("/societe/{id}", name="facture_societe")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function societeAction(Request $request, Societe $societe) {
        $fm = $this->get('facture.manager');
        $facturesRetard = $fm->getRepository()->findRetardDePaiementBySociete($societe,30);

        $formSociete = $this->createForm(SocieteChoiceType::class, array('societe' => $societe), array(
            'action' => $this->generateUrl('societe'),
            'method' => 'GET',
        ));
        $factures = $fm->findBySociete($societe);
        $mouvements = $fm->getMouvementsBySociete($societe);

        return $this->render('facture/societe.html.twig', array('societe' => $societe, 'mouvements' => $mouvements, 'factures' => $factures));
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
            $contrat =  $facture->getContrat();
            if($contrat && $contrat->isBonbleu()){
              $facture->setDescription($contrat->getDescription());
            }
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
        $retour = ($request->get('retour', null));
        $facture = $this->get('facture.manager')->getRepository()->findOneById($factureId);
        $facture->cloturer();
        $dm->persist($facture);
        $dm->flush();

        if($request->isXmlHttpRequest()) {

            return new Response();
        }

        if($retour && ($retour == "relance")){
          return $this->redirectToRoute('factures_retard');
        }
        return $this->redirectToRoute('facture_societe', array('id' => $societe->getId()));
    }

  /**
     * @Route("/avoir/{id}/{factureId}/{mouvement}", name="facture_avoir", defaults={"mouvement" = "1"})
   * @ParamConverter("societe", class="AppBundle:Societe")
   */
  public function avoirAction(Request $request, Societe $societe, $factureId,$mouvement) {
      $dm = $this->get('doctrine_mongodb')->getManager();

      $facture = $this->get('facture.manager')->getRepository()->findOneById($factureId);
      $avoir = $facture->genererAvoir();

      $dm->persist($avoir);
      $dm->flush();

      $facture->setAvoir($avoir->getNumeroFacture());
      $dm->persist($facture);
      $dm->flush();

      if($mouvement){
        $contrat = $facture->getContrat();
        $contrat->restaureMouvements($facture);

        $dm->persist($contrat);
        $dm->flush();
      }

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

        $pages = array();

        $nbLigneMaxPourPageVierge = 50;
        $nbLigneMaxPourDernierePage = 30;
        $nbPage = 1;
        $nbMaxCharByLigne = 60;
        $nbCurrentLigne = 0;
        $nbCurrentPage = 1;
        $nbLigneParLigneFacture = array();
        $nbLigneParPage = array(1 => $nbLigneMaxPourDernierePage);

        foreach($facture->getLignes() as $key => $ligne) {
            $nbCurrentLigne += 2;
            if($ligne->getReferenceClient()) {
                $nbCurrentLigne += 1;
            }

            if($ligne->isOrigineContrat()) {
                $nbCurrentLigne += 4;
                $nbCurrentLigne += count($ligne->getOrigineDocument()->getPrestations());
                $nbCurrentLigne += count($ligne->getOrigineDocument()->getContratPassages());
            }

            $nbLigneParLigneFacture[$key] = $nbCurrentLigne;

            if($nbCurrentPage == $nbPage && $nbCurrentLigne > $nbLigneMaxPourDernierePage) {
                $nbLigneParPage[$nbCurrentPage] = $nbLigneMaxPourDernierePage;
                $nbPage += 1;
                $nbLigneParPage[$nbPage] = $nbLigneMaxPourDernierePage;
            }

            if($nbCurrentPage < $nbPage && $nbCurrentLigne > $nbLigneMaxPourPageVierge) {
                $nbLigneParPage[$nbCurrentPage] = $nbLigneMaxPourPageVierge;
                $nbCurrentPage += 1;
                $nbCurrentLigne = 0;
            }

        }

        $nbCurrentPage = 1;
        $nbCurrentLigne = 0;
        foreach($facture->getLignes() as $key => $ligneFacture) {

            $ligne = $this->buildLignePDFFacture($ligneFacture);

            // La ligne ne tient pas sur une page complète
            if(($nbLigneParLigneFacture[$key]) > $nbLigneParPage[$nbCurrentPage]) {
                $nbLignes2Keep = (int)(0.8 * $nbLigneParPage[$nbCurrentPage]);
                $lignesSplitted = $this->splitLigne($ligne, $nbLignes2Keep);
                $pages[$nbCurrentPage][] = $lignesSplitted[0];
                $pages[$nbCurrentPage+1][] = $lignesSplitted[1];
                $nbCurrentLigne = 0;
                $nbCurrentPage += 1;
                continue;
            }

            // La ligne tient sur la page
            if(($nbCurrentLigne + $nbLigneParLigneFacture[$key]) < $nbLigneParPage[$nbCurrentPage]) {

                $pages[$nbCurrentPage][] = $ligne;
                continue;
            }

            // La ligne ne tient plus sur la page
            if(($nbCurrentLigne + $nbLigneParLigneFacture[$key]) > $nbLigneParPage[$nbCurrentPage]) {

                $nbCurrentLigne = 0;
                $nbCurrentPage += 1;
                $pages[$nbCurrentPage][] = $ligne;
                continue;
            }
        }

        $html = $this->renderView('facture/pdf.html.twig', array(
            'facture' => $facture,
            'pages' => $pages,
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

    public function splitLigne($ligne, $nbLignes2Keep) {
        $ligneSplitted = array();

        $ligneSplitted["libelle"] = $ligne['libelle']." (Suite)";
        foreach($ligne["details"] as $key => $details) {
            if(!preg_match("/^Lieu/", $key)) {
                continue;
            }
            $nb = 0;
            $keySplitted = $key." (Suite)";
            $ligneSplitted["details"] = array();
            $ligneSplitted["details"][$keySplitted] = array();
            foreach($details as $keyLieu => $lieu) {
                $nb += 1;
                if($nb <= $nbLignes2Keep) {
                    continue;
                }
                $ligneSplitted["details"][$keySplitted][] = $lieu;
                unset($ligne["details"][$key][$keyLieu]);
            }
            $ligne["details"][$key][] = "(Suite de la liste sur la page suivante)";
        }

        return array($ligne, $ligneSplitted);
    }

    public function buildLignePDFFacture($ligneFacture) {
        $ligne = array();
        $ligne['libelle'] = $ligneFacture->getLibelle();
        $ligne['quantite'] = $ligneFacture->getQuantite();
        $ligne['prixUnitaire'] = $ligneFacture->getPrixUnitaire();
        $ligne['montantHT'] = $ligneFacture->getMontantHT();
        $ligne['referenceClient'] = $ligneFacture->getReferenceClient();
        if($ligneFacture->isOrigineContrat()) {
            $ligne['details'] = array();

            $keyPrestation = "Prestation";
            if (count($ligneFacture->getOrigineDocument()->getPrestations()) > 1) { $keyPrestation .= "s"; }
            foreach($ligneFacture->getOrigineDocument()->getPrestations() as $prestation) {
                $ligne['details'][$keyPrestation][] = $prestation->getNom();
            }

            $keyPassage = "Lieu";
            if(count($ligneFacture->getOrigineDocument()->getContratPassages()) > 1) { $keyPassage .= "x"; }
            $keyPassage .= " d'application";
            if(count($ligneFacture->getOrigineDocument()->getContratPassages()) > 1) { $keyPassage .= "s"; }
            foreach($ligneFacture->getOrigineDocument()->getContratPassages() as $passage) {
               $lignePassage = $passage->getEtablissement()->getNom(false).", ";
               if($passage->getEtablissement()->getAdresse()->getAdresse()){ $lignePassage .= $passage->getEtablissement()->getAdresse()->getAdresse().", "; }
               $lignePassage .= $passage->getEtablissement()->getAdresse()->getCodePostal()." ".$passage->getEtablissement()->getAdresse()->getCommune();
               $ligne['details'][$keyPassage][] = $lignePassage;
            }
        }

        if($ligneFacture->getDescription()) {
            $ligne["details"]["description"] = $ligneFacture->getDescription();
        }

        return $ligne;
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
     * @Route("/facture/export", name="factures_export")
     */
    public function exportComptableAction(Request $request) {

      // $response = new StreamedResponse();
        $formRequest = $request->request->get('form');

        $dateDebutString = $formRequest['dateDebut']." 00:00:00";
        $dateFinString = $formRequest['dateFin']." 23:59:59";

        $dateDebut = \DateTime::createFromFormat('d/m/Y H:i:s',$dateDebutString);
        $dateFin = \DateTime::createFromFormat('d/m/Y H:i:s',$dateFinString);

        $dm = $this->get('doctrine_mongodb')->getManager();
        $fm = $this->get('facture.manager');
        $facturesForCsv = $fm->getFacturesForCsv($dateDebut,$dateFin);

        $filename = sprintf("export_factures_du_%s_au_%s.csv", $dateDebut->format("Y-m-d"),$dateFin->format("Y-m-d"));
        $handle = fopen('php://memory', 'r+');

        foreach ($facturesForCsv as $paiement) {
            fputcsv($handle, $paiement,';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $response = new Response(utf8_decode($content), 200, array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ));
        $response->setCharset('UTF-8');

        return $response;
    }

    /**
     * @Route("/facture-commerciaux/export", name="commerciaux_export")
     */
    public function exportCommerciauxAction(Request $request) {

      // $response = new StreamedResponse();
        $formRequest = $request->request->get('form');
        $commercial = (isset($formRequest['commercial']) && $formRequest['commercial'] && ($formRequest['commercial']!= ""))?
         $formRequest['commercial'] : null;
        $pdf = (isset($formRequest["pdf"]) && $formRequest["pdf"]);

        $dateDebutString = $formRequest['dateDebut']." 00:00:00";
        $dateFinString = $formRequest['dateFin']." 23:59:59";

        $dateDebut = \DateTime::createFromFormat('d/m/Y H:i:s',$dateDebutString);
        $dateFin = \DateTime::createFromFormat('d/m/Y H:i:s',$dateFinString);

        $dm = $this->get('doctrine_mongodb')->getManager();
        $fm = $this->get('facture.manager');

        $statsForCommerciaux = $fm->getStatsForCommerciauxForCsv($dateDebut,$dateFin,$commercial);

        if(!$pdf){
        $filename = sprintf("export_details_commerciaux_du_%s_au_%s.csv", $dateDebut->format("Y-m-d"),$dateFin->format("Y-m-d"));
        $handle = fopen('php://memory', 'r+');

        foreach ($statsForCommerciaux as $stat) {
            fputcsv($handle, $stat,';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $response = new Response(utf8_decode($content), 200, array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ));
        $response->setCharset('UTF-8');

        return $response;
      }else{
        $html = $this->renderView('facture/pdfStatsCommerciaux.html.twig', array(
          'statsForCommerciaux' => $statsForCommerciaux,
          'dateDebut' => $dateDebut,
          'dateFin' => $dateFin
      ));


      $filename = sprintf("export_stats_commerciaux_du_%s_au_%s.csv.pdf",  $dateDebut->format("Y-m-d"), $dateFin->format("Y-m-d"));

      if ($request->get('output') == 'html') {

          return new Response($html, 200);
       }

      return new Response(
              $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'disable-smart-shrinking' => null,
                 'encoding' => 'utf-8',
                  'margin-left' => 1,
                  'margin-right' => 1,
                  'margin-top' => 1,
                  'margin-bottom' => 1),$this->getPdfGenerationOptions()), 200, array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ));
    }
    }

    public function exportStatsForDates($dateDebutString,$dateFinString,$dateDebut,$dateFin){

      $dateDebutFirstOfMonth = \DateTime::createFromFormat('d/m/Y H:i:s', $dateDebutString);
      $dateDebutFirstOfMonth->modify('first day of this month');
      $dateFinFirstOfMonth = \DateTime::createFromFormat('d/m/Y H:i:s', $dateFinString);
      $dateFinFirstOfMonth->modify('last day of this month');

      $interval = \DateInterval::createFromDateString('1 month');
      $period = new \DatePeriod($dateDebutFirstOfMonth, $interval, $dateFinFirstOfMonth);

      $arrayOfDates = array();
      $cpt = 0;
      $nbPeriod = count(iterator_to_array($period));
      foreach ($period as $dt) {
          $arrayOfDates[$dt->format("Y-m")] = array();
          $firstDay = clone $dt;
          $lastDay = clone $dt;
          $arrayOfDates[$dt->format("Y-m")]['dateDebut'] = $firstDay->modify('first day of this month');
          $arrayOfDates[$dt->format("Y-m")]['dateFin'] = $lastDay->modify('last day of this month +23 hours +59 minutes +59 seconds');
        if(!$cpt){
          $arrayOfDates[$dt->format("Y-m")]['dateDebut'] = $dateDebut;
        }
        $cpt++;
        if($cpt == $nbPeriod){
          $arrayOfDates[$dt->format("Y-m")]['dateFin'] = $dateFin;
        }
      }

      $dm = $this->get('doctrine_mongodb')->getManager();
      $fm = $this->get('facture.manager');

      $completeArray = array();

      $totalArray = array();
      foreach ($arrayOfDates as $dates) {
          $facturesStatsForCsv = $fm->getStatsForCsv($dates['dateDebut'],$dates['dateFin']);
          foreach ($facturesStatsForCsv as $rowId => $paiement) {
            if($rowId == "ENTETE_TITRE"){
              $totalArray[$rowId] = array();
              $totalArray[$rowId][] = array();
              foreach ($paiement as $value) {
                $totalArray[$rowId][] = "";
              }
            }elseif($rowId == "ENTETE"){
              foreach (FactureManager::$export_stats_libelle as $key => $entete) {
                  $totalArray[$rowId][$key] = str_replace('{X}', 'Année courante',$entete);
                  $totalArray[$rowId][$key] = str_replace('{X-1}', 'Année préc.',$totalArray[$rowId][$key]);
              }
            }
            else{
              if(!array_key_exists($rowId,$totalArray)){
                $totalArray[$rowId] = array();
                $totalArray[$rowId] = $paiement;
              }else{
                foreach ($paiement as $key => $value) {
                  if(!$key){
                    $totalArray[$rowId][$key] = $value;
                  }else{
                    $floatValue = floatval(str_replace(array(' ',','),array('','.'),$value));
                    $oldArrayValue = floatval(str_replace(array(' ',','),array('','.'),$totalArray[$rowId][$key]));
                    $totalArray[$rowId][$key] = $oldArrayValue + $floatValue;
                  }
                }
              }
            }
            $completeArray[] = $paiement;
          }
          $completeArray[] = array("","","","","","","","","","");
      }

      $totalArray["ENTETE_TITRE"][0] = sprintf("TOTAL des statistiques du %s au %s", $dateDebut->format("d/m/Y"), $dateFin->format("d/m/Y"));

      if(count($arrayOfDates) > 1){
        foreach ($totalArray as $rowId => $paiement) {
          $tmpArray = array();
          foreach ($paiement as $key => $paiementVal) {
            if(is_numeric($paiementVal)){
              $tmpArray[$key] = number_format($paiementVal, 2, ',', ' ');
            }else{
              $tmpArray[$key] = $paiementVal;
            }
          }
            $completeArray[] = $tmpArray;
        }
        $completeArray[] =  array("","","","","","","","","","");
      }
      return $completeArray;
    }

    /**
     * @Route("/stats/export", name="stats_export")
     */
    public function exportStatsAction(Request $request) {

      // $response = new StreamedResponse();
        ini_set('memory_limit', '-1');
        $formRequest = $request->request->get('form');
        $pdf = (isset($formRequest["pdf"]) && $formRequest["pdf"]);

        $dateDebutString = $formRequest['dateDebut']."00:00:00";
        $dateFinString = $formRequest['dateFin']."23:59:59";

        $dateDebut = \DateTime::createFromFormat('d/m/Y H:i:s',$dateDebutString);
        $dateFin = \DateTime::createFromFormat('d/m/Y H:i:s',$dateFinString);

        $exportStatsArray = $this->exportStatsForDates($dateDebutString,$dateFinString,$dateDebut,$dateFin);

        if(!$pdf){
          $handle = fopen('php://memory', 'r+');
          $filename = sprintf("export_stats_du_%s_au_%s.csv", $dateDebut->format("Y-m-d"), $dateFin->format("Y-m-d"));
          foreach ($exportStatsArray as $paiement) {
              fputcsv($handle, $paiement,';');
            }
          fputcsv($handle, array("",""),';');
          rewind($handle);
          $content = stream_get_contents($handle);
          fclose($handle);
          $response = new Response(utf8_decode($content), 200, array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ));
        $response->setCharset('UTF-8');

        return $response;
      }else{
          $html = $this->renderView('facture/pdfStats.html.twig', array(
            'exportStatsArray' => $exportStatsArray,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ));


        $filename = sprintf("export_stats_du_%s_au_%s.csv.pdf",  $dateDebut->format("Y-m-d"), $dateFin->format("Y-m-d"));

        if ($request->get('output') == 'html') {

            return new Response($html, 200);
         }

        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                  'disable-smart-shrinking' => null,
                   'encoding' => 'utf-8',
                    'margin-left' => 1,
                    'margin-right' => 1,
                    'margin-top' => 1,
                    'margin-bottom' => 1,
                    'orientation' => 'landscape'),$this->getPdfGenerationOptions()), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
          ));
      }
    }

    /**
     * @Route("/retards-de-facture", name="factures_retard")
     */
    public function retardsAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $fm = $this->get('facture.manager');
        $sm = $this->get('societe.manager');
        $pdf = $request->get('pdf',null);
        $dateFactureBasse = null;
        $dateFactureHaute = null;


        $nbRelances = null;
        $societe = null;

        $formFacturesEnRetard = $this->createForm(new FacturesEnRetardFiltresType(), null, array(
            'action' => $this->generateUrl('factures_retard'),
            'method' => 'post',
        ));
        $formFacturesEnRetard->handleRequest($request);
        if ($formFacturesEnRetard->isSubmitted() && $formFacturesEnRetard->isValid()) {

          $formValues =  $formFacturesEnRetard->getData();
          $dateFactureBasse = $formValues["dateFactureBasse"];
          $dateFactureHaute = $formValues["dateFactureHaute"];
          $nbRelances = intval($formValues["nbRelances"]) -1;
          $societe = $formValues["societe"];

        }
        $facturesEnRetard = $fm->getRepository()->findFactureRetardDePaiement($dateFactureBasse, $dateFactureHaute, $nbRelances, $societe);

        $formRelance = $this->createForm(new RelanceType($facturesEnRetard), null, array(
            'action' => $this->generateUrl('factures_relance_massive'),
            'method' => 'post',
        ));

        return $this->render('facture/retardPaiements.html.twig', array('facturesEnRetard' => $facturesEnRetard, "formRelance" => $formRelance->createView(), 'nbRelances' => $nbRelances, 'pdf' => $pdf,
        'formFacturesARelancer' => $formFacturesEnRetard->createView()));
    }


    /**
     * @Route("/relance-massive", name="factures_relance_massive")
     */
    public function relanceMassiveAction(Request $request) {

      set_time_limit(0);
      $dm = $this->get('doctrine_mongodb')->getManager();
      $fm = $this->get('facture.manager');
      $factureARelancer = array();
      $formRequest = $request->request->get('relance');


      foreach ($formRequest as $key => $value) {
        if(preg_match("/^FACTURE-/",$key)){
            $factureARelancer[$key] = $fm->getRepository()->findOneById($key);
        }
      }

      $lignesFacturesRelancees = array();

      foreach ($factureARelancer as $facture) {

          $lignesFacturesRelancees[$facture->getId()] = array();
          foreach($facture->getLignes() as $key => $ligneFacture) {

              $ligne = $this->buildLignePDFFacture($ligneFacture);
              $lignesFacturesRelancees[$facture->getId()][] = $ligne;
          }

          if($facture->getNbRelance() > 2) {
              continue;
          }
          $nbRelance = intval($facture->getNbRelance()) + 1;
          $facture->setNbRelance($nbRelance);
          $dm->flush();
      }


      $html = $this->renderView('facture/pdfRelanceMassive.html.twig', array(
          'facturesRelancees' => $factureARelancer,
          'lignesFacturesRelancees' => $lignesFacturesRelancees,

          'parameters' => $fm->getParameters()
      ));


      $filename = sprintf("relances_massives_%s_%s.pdf", (new \DateTime())->format("Y-m-d_His") , uniqid() );

      if ($request->get('output') == 'html') {

          return new Response($html, 200);
      }
      $path = './pdf/relances/';
      $this->get('knp_snappy.pdf')->generateFromHtml($html, $path.$filename);
      return $this->redirectToRoute('factures_retard',array('pdf' => $filename));

    }

    /**
     * @Route("/relance-pdf/{id}/{numeroRelance}", name="facture_relance_pdf")
     * @ParamConverter("facture", class="AppBundle:Facture")
     */
    public function relancePdfAction(Request $request, Facture $facture, $numeroRelance = 0 ) {

      set_time_limit(0);
      $fm = $this->get('facture.manager');

      $lignes = array();
      foreach($facture->getLignes() as $key => $ligneFacture) {

          $ligne = $this->buildLignePDFFacture($ligneFacture);
          $lignes[] = $ligne;
      }

      $html = $this->renderView('facture/pdfGeneriqueRelance.html.twig', array(
          'facture' => $facture,
          'lignes' => $lignes,
          'numeroRelance' => $numeroRelance,
          'parameters' => $fm->getParameters()
      ));

      $terme_relance = FactureManager::$types_nb_relance[$numeroRelance];

      $filename = sprintf("relance_%s_facture_%s_%s.pdf",$terme_relance, $facture->getNumeroFacture(), (new \DateTime())->format("Y-m-d_His"));

      if ($request->get('output') == 'html') {

          return new Response($html, 200);
      }

      return new Response(
              $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $this->getPdfGenerationOptions()), 200, array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'attachment; filename="' . $filename . '"'
              )
      );

    }

    /**
     * @Route("/relance-commentaire/{id}", name="facture_relance_commentaire_add")
     * @ParamConverter("facture", class="AppBundle:Facture")
     */
    public function relanceCommentaireAction(Request $request, Facture $facture) {

      if (!$request->isXmlHttpRequest()) {
          throw $this->createNotFoundException();
      }

      $dm = $this->get('doctrine_mongodb')->getManager();
      if ($facture) {
          try {
              $facture->setRelanceCommentaire($request->get('value'));
              $dm->persist($facture);
              $dm->flush();
              return new Response(json_encode(array("success" => true)));
          } catch (\Exception $e) {

          }
      }

      throw new \Exception('Une erreur s\'est produite');
      }
}

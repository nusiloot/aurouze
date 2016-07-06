<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Manager\PaiementsManager;
use AppBundle\Type\PaiementsType;
use AppBundle\Document\Paiements;
use AppBundle\Document\Societe;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PaiementsController extends Controller {

    /**
     * @Route("/paiements/liste", name="paiements_liste")
     */
    public function indexAction(Request $request) {

        $paiementsDocs = $this->get('paiements.manager')->getRepository()->getLastPaiements(20);

        $exportForms = $this->createExportsForms();
        return $this->render('paiements/index.html.twig', array('paiementsDocs' => $paiementsDocs,'exportForms' => $exportForms));
    }

    /**
     * @Route("/paiements/societe/{id}", name="paiements_societe")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function societeAction(Request $request, Societe $societe) {

        $paiementsDocs = $this->get('paiements.manager')->getRepository()->getBySociete($societe);

        return $this->render('paiements/societe.html.twig', array('paiementsDocs' => $paiementsDocs, 'societe' => $societe));
    }

    /**
     * @Route("/paiements/{id}/modification", name="paiements_modification")
     * @ParamConverter("paiements", class="AppBundle:Paiements")
     */
    public function modificationAction(Request $request, $paiements) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $facturesIds = $paiements->getFacturesArrayIds();
        $facturesArray = $paiements->getFacturesArray();
        $form = $this->createForm(new PaiementsType($this->container, $dm), $paiements, array(
            'action' => $this->generateUrl('paiements_modification', array('id' => $paiements->getId())),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paiements = $form->getData();

            $dm->persist($paiements);
            $dm->flush();

            $facturesIds = array_merge($facturesIds,$paiements->getFacturesArrayIds());

            array_unique($facturesIds);

            foreach ($facturesIds as $factureId) {
               $facture = $dm->getRepository('AppBundle:Facture')->findOneById($factureId);
               $facture->updateMontantPaye();
               $dm->persist($facture);
               $dm->flush();
            }

            return $this->redirectToRoute('paiements_modification', array('id' => $paiements->getId()));
        }

        return $this->render('paiements/modification.html.twig', array('paiements' => $paiements, 'form' => $form->createView(), 'facturesArray' => $facturesArray));
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

    /**
     * @Route("/paiements/export", name="paiements_export")
     */
    public function exportComptableAction(Request $request) {

      // $response = new StreamedResponse();
        $formRequest = $request->request->get('form');
        $dateDebut = \DateTime::createFromFormat('d/m/Y',$formRequest['dateDebut']);
        $dateFin = \DateTime::createFromFormat('d/m/Y',$formRequest['dateFin']);
        $dm = $this->get('doctrine_mongodb')->getManager();
        $pm = $this->get('paiements.manager');
        $paiementsForCsv = $pm->getPaiementsForCsv($dateDebut,$dateFin);

        $filename = sprintf("export_paiements_du_%s_au_%s.csv", $dateDebut->format("Y-m-d"),$dateFin->format("Y-m-d"));
        $handle = fopen('php://memory', 'r+');

        foreach ($paiementsForCsv as $paiement) {
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
     * @Route("/paiements/{id}/banque", name="paiements_export_banque")
     * @ParamConverter("paiements", class="AppBundle:Paiements")
     */
    public function pdfBanqueAction(Request $request, Paiements $paiements) {


        $dm = $this->get('doctrine_mongodb')->getManager();
        $pm = $this->get('paiements.manager');
        $paiementsLists = array();
        $page = 0;
        $cpt = 0;
        foreach ($paiements->getPaiementUniqueParLibelle() as $paiement) {
          if($cpt % 30 == 0){
            $page++;
            $paiementsLists[$page] = array();
          }
          $paiementsLists[$page][] = $paiement;
          $cpt++;
        }


        $html = $this->renderView('paiements/pdfBanque.html.twig', array(
            'paiements' => $paiements,
            'paiementsLists' => $paiementsLists,
            'parameters' => $pm->getParameters(),
        ));


        $filename = sprintf("banque_paiements_%s.pdf", $paiements->getDateCreation()->format("Y-m-d"));

        if ($request->get('output') == 'html') {

            return new Response($html, 200);
        }

        $paiements->setImprime(true);
        $dm->persist($paiements);
        $dm->flush();

        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $this->getPdfGenerationOptions()), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                )
        );
    }

    public function getPdfGenerationOptions() {
        return array('disable-smart-shrinking' => null, 'encoding' => 'utf-8', 'margin-left' => 3, 'margin-right' => 3, 'margin-top' => 4, 'margin-bottom' => 4, 'zoom' => 1);
    }

    private function createExportsForms(){

      $exportsTypes = PaiementsManager::$types_exports;
      $exportForms = array();
      foreach ($exportsTypes as $exporttype => $type_export) {
        $exportForms[$exporttype] = new \stdClass();
        $exportForms[$exporttype]->type = $exporttype;
        $exportForms[$exporttype]->libelle = $type_export['libelle'];
        $exportForms[$exporttype]->picto = $type_export['picto'];
        $formBuilder = $this->createFormBuilder(array());
            $formBuilder->add('dateDebut', DateType::class, array('required' => true,
                "attr" => array('class' => 'input-inline datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd/mm/yyyy'
                    ),
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date de début* :',
            ));
        $formBuilder->add('dateFin', DateType::class, array('required' => true,
                "attr" => array('class' => 'input-inline datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd/mm/yyyy'
                    ),
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date de fin* :',
            ));
        $formBuilder->setAction($this->generateUrl($exporttype.'_export'));
        $form = $formBuilder->getForm();

        $exportForms[$exporttype]->form = $form->createView();
      }
      return $exportForms;
    }

}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\PaiementsType;
use AppBundle\Document\Paiements;
use AppBundle\Document\Societe;

class PaiementsController extends Controller {

    /**
     * @Route("/paiements/liste", name="paiements_liste")
     */
    public function indexAction(Request $request) {

        $paiementsDocs = $this->get('paiements.manager')->getRepository()->getLastPaiements(10);

        return $this->render('paiements/index.html.twig', array('paiementsDocs' => $paiementsDocs));
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

    /**
     * @Route("/paiements/export-comptable", name="paiements_export_comtable")
     */
    public function exportComptableAction(Request $request) {

      // $response = new StreamedResponse();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $pm = $this->get('paiements.manager');
        $paiementsForCsv = $pm->getPaiementsForCsv();

        $filename = sprintf("export_paiements_%s.csv", (new \DateTime())->format("Y-m-d"));
        $handle = fopen('php://memory', 'r+');
        foreach ($paiementsForCsv as $paiement) {
            fputcsv($handle, $paiement);
        }
 
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);



        $response = new Response($content, 200, array(
            'Content-Type' => 'application/force-download',
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
        $html = $this->renderView('paiements/pdfBanque.html.twig', array(
            'paiements' => $paiements,
            'parameters' => $pm->getParameters(),
        ));


        $filename = sprintf("banque_paiements_%s.pdf", $paiements->getDateCreation()->format("Y-m-d"));

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

    public function getPdfGenerationOptions() {
        return array('disable-smart-shrinking' => null, 'encoding' => 'utf-8', 'margin-left' => 3, 'margin-right' => 3, 'margin-top' => 4, 'margin-bottom' => 4, 'zoom' => 1);
    }

}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Document\UserInfos;
use AppBundle\Type\ContratType;
use AppBundle\Manager\ContratManager;
use Knp\Snappy\Pdf;

class ContratController extends Controller {

    /**
     * @Route("/contrat/{id}/creation", name="contrat_creation")
     */
    public function creationAction(Request $request, $id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissement = $dm->getRepository('AppBundle:Etablissement')->findOneById($id);
        $contrat = $this->get('contrat.manager')->create($etablissement);
        $dm->persist($contrat);
        $dm->flush();
        return $this->redirectToRoute('contrat_modification', array('id' => $contrat->getId()));
    }

    /**
     * @Route("/contrat/{id}/modification", name="contrat_modification")
     */
    public function modificationAction(Request $request, $id) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $contratManager = new ContratManager($dm);
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($id);

        $form = $this->createForm(new ContratType($this->container, $dm), $contrat, array(
            'action' => $this->generateUrl('contrat_modification', array('id' => $id)),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contrat = $form->getData();
            $contrat->setStatut(ContratManager::STATUT_EN_ATTENTE_ACCEPTATION);
            $nextPassage = $contratManager->getNextPassgeForContrat($contrat);
            if ($nextPassage) {
                $contrat->addPassage($nextPassage);
                $dm->persist($nextPassage);
            }
            $dm->persist($contrat);
            $dm->flush();
            return $this->redirectToRoute('contrat_validation', array('id' => $contrat->getId()));
        }
        return $this->render('contrat/modification.html.twig', array('contrat' => $contrat, 'form' => $form->createView()));
    }

    /**
     * @Route("/contrat/{id}/validation", name="contrat_validation")
     */
    public function validationAction(Request $request, $id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($id);
        if ($request->get('valide')) {
            $contrat->setStatut(Contrat::STATUT_VALIDE);
            $dm->persist($contrat);
            $dm->flush();
            return $this->redirectToRoute('contrat_visualisation', array('id' => $contrat->getId()));
        }
        return $this->render('contrat/validation.html.twig', array('contrat' => $contrat));
    }

    /**
     * @Route("/contrat/{id}/visualisation", name="contrat_visualisation")
     */
    public function visualisationAction(Request $request, $id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($id);
        return $this->render('contrat/visualisation.html.twig', array('contrat' => $contrat));
    }

    /**
     * @Route("/contrat/{id}/suppression", name="contrat_suppression")
     */
    public function suppressionAction(Request $request, $id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($id);
        $etablissementId = $contrat->getEtablissement()->getId();
        $dm->remove($contrat);
        $dm->flush();
        return $this->redirectToRoute('passage_etablissement', array('id' => $etablissementId));
    }

    /**
     * @Route("/contrat/{id}/pdf", name="contrat_pdf")
     */
    public function pdfAction(Request $request, $id) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($id);

        $contratVisuUrl =  $this->generateUrl('contrat_visualisation', array('id' => $contrat->getId()), true);
//        $html = $this->renderView('contrat/validation.html.twig', array('contrat' => $contrat));
        
//        return $this->render('contrat/validation.html.twig', array('contrat' => $contrat));
        
        $fileName = "AUROUZE_" . $contrat->getId() . ".pdf";
        return new Response(
                $this->container->get('knp_snappy.pdf')->getOutput($contratVisuUrl), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                )
        );
    }

}

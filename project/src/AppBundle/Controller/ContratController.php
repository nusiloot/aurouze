<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Document\UserInfos;
use AppBundle\Type\SocieteChoiceType;
use AppBundle\Type\ContratType;
use AppBundle\Type\ContratAcceptationType;
use AppBundle\Manager\ContratManager;
use Knp\Snappy\Pdf;

class ContratController extends Controller {

    /**
     * @Route("/contrat", name="contrat")
     */
    public function indexAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $formSociete = $this->createForm(SocieteChoiceType::class, array(), array(
            'action' => '',
            'method' => 'POST',
        ));

        return $this->render('contrat/index.html.twig', array('formSociete' => $formSociete->createView()));
    }

    /**
     * @Route("/contrat/{id}/creation", name="contrat_creation")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function creationAction(Request $request, Etablissement $etablissement) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $this->get('contrat.manager')->create($etablissement);
        $dm->persist($contrat);
        $dm->flush();
        return $this->redirectToRoute('contrat_modification', array('id' => $contrat->getId()));
    }

    /**
     * @Route("/contrat/{id}/modification", name="contrat_modification")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function modificationAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $form = $this->createForm(new ContratType($this->container, $dm), $contrat, array(
            'action' => $this->generateUrl('contrat_modification', array('id' => $contrat->getId())),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contrat = $form->getData();
            $contrat->setStatut(ContratManager::STATUT_EN_ATTENTE_ACCEPTATION);
            $contrat->updateObject();
            $contrat->updatePrestations($dm);
            $contrat->updateProduits($dm);
            $dm->persist($contrat);
            $dm->flush();
            return $this->redirectToRoute('contrat_acceptation', array('id' => $contrat->getId()));
        }
        return $this->render('contrat/modification.html.twig', array('contrat' => $contrat, 'form' => $form->createView()));
    }

    /**
     * @Route("/contrat/{id}/acceptation", name="contrat_acceptation")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function acceptationAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contratManager = new ContratManager($dm);
        $form = $this->createForm(new ContratAcceptationType($dm), $contrat, array(
            'action' => $this->generateUrl('contrat_acceptation', array('id' => $id)),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contrat = $form->getData();
            $contratManager->generateAllPassagesForContrat($contrat);

            $contrat->setStatut(ContratManager::STATUT_VALIDE);
            $dm->persist($contrat);
            $dm->flush();
            return $this->redirectToRoute('contrat_visualisation', array('id' => $contrat->getId()));
        }
        return $this->render('contrat/acceptation.html.twig', array('contrat' => $contrat,'form' => $form->createView()));

    }

    /**
     * @Route("/contrat/{id}/visualisation", name="contrat_visualisation")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function visualisationAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        return $this->render('contrat/visualisation.html.twig', array('contrat' => $contrat));
    }

    /**
     * @Route("/contrat/{id}/suppression", name="contrat_suppression")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function suppressionAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissementId = $contrat->getEtablissement()->getId();
        $dm->remove($contrat);
        $dm->flush();
        return $this->redirectToRoute('passage_etablissement', array('id' => $etablissementId));
    }

    /**
     * @Route("/contrat/{id}/generation-mouvement", name="contrat_generation_mouvement")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function generationMouvementAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat->generateMouvement();
        $dm->persist($contrat);
        $dm->flush();

        return $this->redirectToRoute('contrat_visualisation', array('id' => $contrat->getId()));
    }

    /**
     * @Route("/contrat/{id}/pdf", name="contrat_pdf")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function pdfAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();

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

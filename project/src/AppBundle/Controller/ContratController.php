<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Societe;
use AppBundle\Document\Contrat;
use AppBundle\Type\SocieteChoiceType;
use AppBundle\Type\ContratType;
use AppBundle\Type\ContratMarkdownType;
use AppBundle\Type\ContratGeneratorType;
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
            'action' => $this->generateUrl('contrat_societe_choice'),
            'method' => 'POST',
        ));
        $formSociete->handleRequest($request);

        return $this->render('contrat/index.html.twig', array('formSociete' => $formSociete->createView()));
    }

    /**
     * @Route("/contrat/societe-choix", name="contrat_societe_choice")
     */
    public function societeChoiceAction(Request $request) {
        $formData = $request->get('societe_choice');
        return $this->redirectToRoute('contrats_societe', array('id' => $formData['societes']));
    }

    /**
     * @Route("/contrat/{id}/societe", name="contrats_societe")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function societeAction(Request $request, Societe $societe) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $contrats = $this->get('contrat.manager')->getRepository()->findBy(array('societe' => $societe->getId()), array('dateDebut' => 'DESC'));
        
        usort($contrats, array("AppBundle\Document\Contrat", "cmpContrat"));
        
        $formSociete = $this->createForm(SocieteChoiceType::class, array('societes' => $societe->getIdentifiant(), 'societe' => $societe), array(
            'action' => $this->generateUrl('contrat_societe_choice'),
            'method' => 'POST',
        ));

        return $this->render('contrat/societe.html.twig', array('formSociete' => $formSociete->createView(), 'societe' => $societe, 'contrats' => $contrats));
    }

    /**
     * @Route("/contrat/{id}/creation", name="contrat_creation")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function creationAction(Request $request, Societe $societe) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $this->get('contrat.manager')->createBySociete($societe);
        $dm->persist($contrat);
        $dm->flush();
        return $this->redirectToRoute('contrat_modification', array('id' => $contrat->getId()));
    }

    /**
     * @Route("/contrat/{id}/creation/etablissement", name="contrat_creation_etablissement")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function creationFromEtablissementAction(Request $request, Etablissement $etablissement) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $contrat = $this->get('contrat.manager')->createBySociete($etablissement->getSociete(), null, $etablissement);
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

        if (!$contrat->isModifiable()) {
            throw $this->createNotFoundException();
        }

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
        return $this->render('contrat/modification.html.twig', array('contrat' => $contrat, 'form' => $form->createView(), 'societe' => $contrat->getSociete()));
    }

    /**
     * @Route("/contrat/{id}/acceptation", name="contrat_acceptation")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function acceptationAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (!$contrat->isModifiable()) {
            throw $this->createNotFoundException();
        }

        $contratManager = new ContratManager($dm);
        $oldTechnicien = $contrat->getTechnicien();
        $form = $this->createForm(new ContratAcceptationType($dm, $contrat), $contrat, array(
            'action' => $this->generateUrl('contrat_acceptation', array('id' => $contrat->getId())),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $contrat = $form->getData();
            if ($contrat->isModifiable()) {
                $contratManager->generateAllPassagesForContrat($contrat);
                $contrat->setDateFin($contrat->getDateDebut()->modify("+" . $contrat->getDuree() . " month"));
                $contrat->setStatut(ContratManager::STATUT_EN_COURS);
                $dm->persist($contrat);
                $dm->flush();
                return $this->redirectToRoute('contrat_visualisation', array('id' => $contrat->getId()));
            } else {
                if ((!$oldTechnicien) || $oldTechnicien->getId() != $contrat->getTechnicien()->getId()) {
                    $contrat->changeTechnicien($contrat->getTechnicien());
                }
                $dm->persist($contrat);
                $dm->flush();
                return $this->redirectToRoute('passage_etablissement', array('id' => $contrat->getEtablissements()->first()->getId()));
            }
        }
        return $this->render('contrat/acceptation.html.twig', array('contrat' => $contrat, 'form' => $form->createView(), 'societe' => $contrat->getSociete()));
    }

    /**
     * @Route("/contrat/{id}/visualisation", name="contrat_visualisation")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function visualisationAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        return $this->render('contrat/visualisation.html.twig', array('contrat' => $contrat, 'societe' => $contrat->getSociete()));
    }

    /**
     * @Route("/contrat/{id}/markdown", name="contrat_markdown")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function markdownAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (!$contrat->getMarkdown()) {
            $contrat->setMarkdown($this->renderView('contrat/contrat.markdown.twig', array('contrat' => $contrat)));
            $dm->persist($contrat);
            $dm->flush();
        }

        $formMarkdown = $this->createForm(new ContratMarkdownType(), $contrat, array(
            'action' => $this->generateUrl('contrat_markdown', array('id' => $contrat->getId())),
            'method' => 'POST',
        ));

        $formMarkdown->handleRequest($request);
        if ($formMarkdown->isSubmitted() && $formMarkdown->isValid()) {
            $contrat = $formMarkdown->getData();
            $dm->persist($contrat);
            $dm->flush();
        }

        $formGenerator = $this->createForm(new ContratGeneratorType(), $contrat, array(
            'action' => $this->generateUrl('contrat_markdown', array('id' => $contrat->getId())),
            'method' => 'POST',
        ));

        $formGenerator->handleRequest($request);
        if ($formGenerator->isSubmitted() && $formGenerator->isValid()) {
            $contrat = $formGenerator->getData();
            $contrat->setMarkdown($this->renderView('contrat/contrat.markdown.twig', array('contrat' => $contrat)));
            $dm->persist($contrat);
            $dm->flush();
        }


        return $this->render('contrat/visualisation.markdown.twig', array('contrat' => $contrat, 'formMarkdown' => $formMarkdown->createView(), 'formGenerator' => $formGenerator->createView()));
    }

    /**
     * @Route("/contrat/{id}/suppression", name="contrat_suppression")
     * @ParamConverter("contrat", class="AppBundle:Contrat")
     */
    public function suppressionAction(Request $request, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (!$contrat->isModifiable()) {
            throw $this->createNotFoundException();
        }
        $societeId = $contrat->getSociete()->getId();
        foreach ($contrat->getContratPassages() as $contratPassages) {
            foreach ($contratPassages->getPassages() as $passage) {
                $dm->remove($passage);
            }
        }
        $dm->remove($contrat);
        $dm->flush();
        return $this->redirectToRoute('contrats_societe', array('id' => $societeId));
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


    	$contrat->setMarkdown($this->renderView('contrat/contrat.markdown.twig', array('contrat' => $contrat)));
    	$dm->persist($contrat);
    	$dm->flush();

    	$header =  $this->renderView('contrat/pdf-header.html.twig', array(
    			'contrat' => $contrat
    	));
    	$footer =  $this->renderView('contrat/pdf-footer.html.twig', array(
    			'contrat' => $contrat
    	));
    	$html =  $this->renderView('contrat/pdf.html.twig', array(
    			'contrat' => $contrat
    	));
    	if($request->get('output') == 'html') {

    		return new Response($html, 200);
    	}

    	return new Response(
    			$this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
    						'footer-html' => $footer,
    						'header-html' => $header,
    						'margin-right'  => 0,
    						'margin-left'   => 0,
    						'margin-top'   => 38,
    						'margin-bottom'   => 38,
    						'page-size' => "A4"
    			)),
    			200,
    			array(
    					'Content-Type'          => 'application/pdf',
    					'Content-Disposition'   => 'attachment; filename="contrat-'.$contrat->getNumeroArchive().'.pdf"'
    			)
    			);
    }

}

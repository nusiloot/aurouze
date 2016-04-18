<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\EtablissementChoiceType;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Passage;
use AppBundle\Type\PassageType;
use AppBundle\Type\PassageCreationType;
use AppBundle\Manager\ContratManager;

class PassageController extends Controller {

    /**
     * @Route("/passage", name="passage")
     */
    public function indexAction(Request $request) {
        $formEtablissement = $this->createForm(EtablissementChoiceType::class, null, array(
            'action' => $this->generateUrl('passage_etablissement_choice'),
            'method' => 'POST',
        ));

        $passages = $this->get('passage.manager')->getRepository()->findToPlan();

        $geojson = $this->buildGeoJson($passages);

        return $this->render('passage/index.html.twig', array('passages' => $passages, 'formEtablissement' => $formEtablissement->createView(), 'geojson' => $geojson));
    }

    /**
     * @Route("/passage/{id}/creer", name="passage_creation")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function creationAction(Request $request, Etablissement $etablissement) {
        $dm = $this->get('doctrine_mongodb')->getManager();
		$passage = $this->get('passage.manager')->create($etablissement);

		$form = $this->createForm(new PassageCreationType($dm), $passage, array(
				'action' => $this->generateUrl('passage_creation', array('id' => $etablissement->getId())),
				'method' => 'POST',
		));
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$passage = $form->getData();
			$dm->persist($passage);
			$dm->flush();
			return $this->redirectToRoute('passage_etablissement', array('id' => $etablissement->getId()));
		}

        return $this->render('passage/creation.html.twig', array('passage' => $passage, 'form' => $form->createView()));
    }

    /**
     * @Route("/passage/{id}/modifier", name="passage_modification")
     * @ParamConverter("passage", class="AppBundle:Passage")
     */
    public function modificationAction(Request $request, Passage $passage) {
        $dm = $this->get('doctrine_mongodb')->getManager();

		$form = $this->createForm(new PassageCreationType($dm), $passage, array(
				'action' => $this->generateUrl('passage_modification', array('id' => $passage->getId())),
				'method' => 'POST',
		));
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$passage = $form->getData();
			$dm->persist($passage);
			$dm->flush();
			return $this->redirectToRoute('passage_etablissement', array('id' => $passage->getEtablissement()->getId()));
		}

        return $this->render('passage/modification.html.twig', array('passage' => $passage, 'form' => $form->createView()));
    }

    /**
     * @Route("/passage/etablissement-choix", name="passage_etablissement_choice")
     */
    public function etablissementChoiceAction(Request $request) {
        $formData = $request->get('etablissement_choice');

        return $this->redirectToRoute('passage_etablissement', array('id' => $formData['etablissements']));
    }

    /**
     * @Route("/passages/{id}/etablissement", name="passage_etablissement")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function etablissementAction(Request $request, Etablissement $etablissement) {

        $contrats = $this->get('contrat.manager')->getRepository()->findByEtablissement($etablissement);

        $geojson = $this->buildGeoJson(array($etablissement));
        $formEtablissement = $this->createForm(EtablissementChoiceType::class, array('etablissements' => $etablissement->getIdentifiant(), 'etablissement' => $etablissement), array(
            'action' => $this->generateUrl('passage_etablissement_choice'),
            'method' => 'POST',
        ));

        return $this->render('passage/etablissement.html.twig', array('etablissement' => $etablissement, 'contrats' => $contrats, 'formEtablissement' => $formEtablissement->createView(), 'geojson' => $geojson));
    }

    /**
     * @Route("/passage/edition/{id}", name="passage_edition")
     * @ParamConverter("passage", class="AppBundle:Passage")
     */
    public function editionAction(Request $request, Passage $passage) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $form = $this->createForm(new PassageType(), $passage, array(
            'action' => $this->generateUrl('passage_edition', array('id' => $passage->getId())),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            return $this->render('passage/edition.html.twig', array('passage' => $passage, 'form' => $form->createView()));
        }

        $contratManager = new ContratManager($dm);
        $contrat = $passage->getContrat();

        $nextPassage = $contratManager->getNextPassageForContrat($contrat);

        if ($nextPassage) {
            $contrat->addPassage($nextPassage);
            $dm->persist($nextPassage);
        }

        $dm->flush();



        return $this->redirectToRoute('passage_etablissement', array('id' => $passage->getEtablissementId()));
    }

    /**
     * @Route("/etablissement-all", name="etablissement_all")
     */
    public function allAction(Request $request) {
        $etablissementsResult = $this->get('etablissement.manager')->getRepository()->findBy(array(), null, 3000);
        $geojson = $this->buildGeoJson($etablissementsResult);
        return $this->render('etablissement/all.html.twig', array('geojson' => $geojson));
    }

    private function buildGeoJson($listDocuments) {
        $geojson = new \stdClass();
        $geojson->type = "FeatureCollection";
        $geojson->features = array();
        foreach ($listDocuments as $document) {
            $feature = new \stdClass();
            $feature->type = "Feature";
            $feature->properties = new \stdClass();
            $feature->properties->_id = $document->getId();
            $etbInfos = $document;
            $coordinates = null;
            if (!($document instanceof Etablissement)) {
                $allTechniciens = $document->getTechniciens();
                $firstTechnicien = null;
                foreach ($allTechniciens as $technicien) {
                    $firstTechnicien = $technicien;
                    break;
                }
                $etbInfos = $document->getEtablissementInfos();
                $coordinates = $document->getEtablissementInfos()->getAdresse()->getCoordonnees();
                $feature->properties->color = $firstTechnicien->getCouleur();
                $feature->properties->colorText = $firstTechnicien->getCouleurText();
            } else {
                $coordinates = $document->getAdresse()->getCoordonnees();
                $feature->properties->color = "#fff";
                $feature->properties->colorText = "#000";
            }
            if (!$coordinates->getLon() || !$coordinates->getLat()) {
                continue;
            }
            $feature->properties->nom = $etbInfos->getNom();
            $feature->properties->icon = 'mdi-' . $etbInfos->getIcon();
            $feature->geometry = new \stdClass();
            $feature->geometry->type = "Point";
            $feature->geometry->coordinates = array($coordinates->getLon(), $coordinates->getLat());
            $geojson->features[] = $feature;
        }
        return $geojson;
    }

}

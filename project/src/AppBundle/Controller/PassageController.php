<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\EtablissementChoiceType;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Document\Passage;
use AppBundle\Type\PassageType;
use AppBundle\Type\PassageCreationType;
use AppBundle\Manager\PassageManager;
use Behat\Transliterator\Transliterator;
use AppBundle\Type\InterventionRapideCreationType;
use AppBundle\Manager\ContratManager;
use AppBundle\Document\Prestation;

class PassageController extends Controller {

    /**
     * @Route("/passage", name="passage")
     */
    public function indexAction(Request $request) {
        $formEtablissement = $this->createForm(EtablissementChoiceType::class, null, array(
            'action' => $this->generateUrl('passage_etablissement_choice'),
            'method' => 'GET',
        ));

        $passages = $this->get('passage.manager')->getRepository()->findToPlan();
        $moisPassagesArray = $this->get('passage.manager')->getRepository()->getNbPassagesToPlanPerMonth();
        
        $geojson = $this->buildGeoJson($passages);

        return $this->render('passage/index.html.twig', array('passages' => $passages, 'formEtablissement' => $formEtablissement->createView(), 'geojson' => $geojson, 'moisPassagesArray' => $moisPassagesArray));
    }

    /**
     * @Route("/passage/{id_etablissement}/{id_contrat}/creer", name="passage_creation")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement", options={"id" = "id_etablissement"})
     * @ParamConverter("contrat", class="AppBundle:Contrat", options={"id" = "id_contrat"})
     */
    public function creationAction(Request $request, Etablissement $etablissement, Contrat $contrat) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $passage = $this->get('passage.manager')->create($etablissement, $contrat);

        $form = $this->createForm(new PassageCreationType($dm), $passage, array(
            'action' => $this->generateUrl('passage_creation', array('id_etablissement' => $etablissement->getId(), 'id_contrat' => $contrat->getId())),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $passage = $form->getData();
            $passage->setDatePrevision($passage->getDateDebut());
            $dm->persist($passage);
            $contrat->addPassage($etablissement, $passage);
            $dm->persist($contrat);
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

        $passageManager = new PassageManager($dm);

        $nextPassage = $passageManager->getNextPassageFromPassage($passage);
        if ($nextPassage) {
            $nextPassage->setDateDebut($nextPassage->getDatePrevision());
            $nextPassage->copyTechnicienFromPassage($passage);

            $dm->persist($nextPassage);
        }
        $passage->setDateRealise($passage->getDateDebut());
        $dm->persist($passage);
        $dm->flush();

        $contratIsFini = true;
        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($passage->getContrat()->getId());
        foreach ($passage->getContrat()->getContratPassages() as $contratPassages) {
            foreach ($contratPassages->getPassages() as $passage) {
                if (!$passage->isAnnule() && !$passage->isRealise()) {
                    $contratIsFini = false;
                    break;
                }
            }
        }
        if ($contratIsFini) {
            $contrat->setStatut(ContratManager::STATUT_FINI);
        }
        $dm->persist($contrat);
        $dm->flush();



        return $this->redirectToRoute('passage_etablissement', array('id' => $passage->getEtablissement()->getId()));
    }

    public function getPdfGenerationOptions() {
        return array('disable-smart-shrinking' => null, 'encoding' => 'utf-8', 'margin-left' => 3, 'margin-right' => 3, 'margin-top' => 4, 'margin-bottom' => 4, 'zoom' => 0.8);
    }

    /**
     * @Route("/passage/pdf-bon/{id}", name="passage_pdf_bon")
     * @ParamConverter("passage", class="AppBundle:Passage")
     */
    public function pdfBonAction(Request $request, Passage $passage) {
        $fm = $this->get('facture.manager');

        $html = $this->renderView('passage/pdfBons.html.twig', array(
            'passage' => $passage,
            'parameters' => $fm->getParameters(),
        ));

        $filename = sprintf("bon_passage_%s_%s.pdf", $passage->getDateDebut()->format("Y-m-d_H:i"), strtoupper(Transliterator::urlize($passage->getTechniciens()->first()->getIdentite())));

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
     * @Route("/passage/pdf-bons-massif", name="passage_pdf_bons_massif")
     */
    public function pdfBonsMassifAction(Request $request) {
        $fm = $this->get('facture.manager');
        $pm = $this->get('passage.manager');
        $dm = $this->get('doctrine_mongodb')->getManager();

        if ($request->get('technicien')) {
            $technicien = $dm->getRepository('AppBundle:Compte')->findOneById($request->get('technicien'));
            $passages = $pm->getRepository()->findAllPlanifieByPeriodeAndIdentifiantTechnicien($request->get('dateDebut'), $request->get('dateFin'), $technicien);
            $filename = sprintf("bons_passage_%s_%s_%s.pdf", $request->get('dateDebut'), $request->get('dateFin'), strtoupper(Transliterator::urlize($technicien->getIdentite())));
        } else {
            $passages = $pm->getRepository()->findAllPlanifieByPeriode($request->get('dateDebut'), $request->get('dateFin'));
            $filename = sprintf("bons_passage_%s_%s.pdf", $request->get('dateDebut'), $request->get('dateFin'));
        }

        $html = $this->renderView('passage/pdfBonsMassif.html.twig', array(
            'passages' => $passages,
            'parameters' => $fm->getParameters(),
        ));

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
     * @Route("/passage/pdf-mission/{id}", name="passage_pdf_mission")
     * @ParamConverter("passage", class="AppBundle:Passage")
     */
    public function pdfMissionAction(Request $request, Passage $passage) {
        $pm = $this->get('passage.manager');

        $passagesHistory = $pm->getRepository()->findHistoriqueByEtablissementAndPrestations($passage->getEtablissement(), $passage->getPrestations());

        $filename = sprintf("suivi_client_%s_%s.pdf", $passage->getDateDebut()->format("Y-m-d_H:i"), strtoupper(Transliterator::urlize($passage->getTechniciens()->first()->getIdentite())));

        $html = $this->renderView('passage/pdfMission.html.twig', array(
            'passage' => $passage,
            'passagesHistory' => $passagesHistory,
        ));

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
     * @Route("/passage/pdf-missions-massif", name="passage_pdf_missions_massif")
     */
    public function pdfMissionsMassifAction(Request $request) {
        $fm = $this->get('facture.manager');
        $pm = $this->get('passage.manager');
        $dm = $this->get('doctrine_mongodb')->getManager();

        if ($request->get('technicien')) {
            $technicien = $dm->getRepository('AppBundle:Compte')->findOneById($request->get('technicien'));
            $passages = $pm->getRepository()->findAllPlanifieByPeriodeAndIdentifiantTechnicien($request->get('dateDebut'), $request->get('dateFin'), $technicien);
            $filename = sprintf("suivis_client_%s_%s_%s.pdf", $request->get('dateDebut'), $request->get('dateFin'), strtoupper(Transliterator::urlize($technicien->getIdentite())));
        } else {
            $passages = $pm->getRepository()->findAllPlanifieByPeriode($request->get('dateDebut'), $request->get('dateFin'));
            $filename = sprintf("suivis_client_%s_%s.pdf", $request->get('dateDebut'), $request->get('dateFin'));
        }

        $passagesHistories = array();

        foreach ($passages as $passage) {
            $passagesHistories[$passage->getId()] = $pm->getRepository()->findHistoriqueByEtablissementAndPrestations($passage->getEtablissement(), $passage->getPrestations());
        }

        $html = $this->renderView('passage/pdfMissionsMassif.html.twig', array(
            'passages' => $passages,
            'passagesHistories' => $passagesHistories,
        ));

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
                $feature->properties->color = 'black';
                $feature->properties->colorText = 'white';
                if (!is_null($firstTechnicien)) {

                    $feature->properties->color = $firstTechnicien->getCouleur();
                    $feature->properties->colorText = $firstTechnicien->getCouleurText();
                }
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

    /**
     * @Route("/passages/{id}/creation-rapide", name="passage_creation_rapide")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function creationRapideAction(Request $request, Etablissement $etablissement) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $newContrat = new Contrat();
        $newContrat->setSociete($etablissement->getSociete());
        $newContrat->setDateCreation(new \DateTime());
        $prestation = new Prestation();
        $prestation->setNbPassages(1);
        $newContrat->setDuree("1");
        $newContrat->addPrestation($prestation);
        $configurationPrestationArray = $dm->getRepository('AppBundle:Configuration')->findConfiguration()->getPrestationsArray();

        $form = $this->createForm(new InterventionRapideCreationType($dm), $newContrat, array(
            'action' => $this->generateUrl('passage_creation_rapide', array('id' => $etablissement->getId())),
            'method' => 'POST',
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameters = $request->get('interventionRapide');
            $newContrat->setTypeContrat(ContratManager::TYPE_CONTRAT_PONCTUEL);
            $dateDebut = clone $newContrat->getDateDebut();
            $newContrat->setDateFin($dateDebut->modify("+" . $newContrat->getDuree() . " month"));
            $newContrat->setDureeGarantie(0);
            $newContrat->setPrixHt(null);
            $newContrat->setTvaReduite(false);
            $dm->persist($newContrat);

            $newPassage = new Passage();
            $newPassage->setEtablissement($etablissement);
            $newPassage->setDatePrevision($newContrat->getDateDebut());
            foreach ($parameters['prestations'] as $prestationParam) {
                $prestationIdentifiant = $prestationParam['identifiant'];
                $prestation = clone $configurationPrestationArray[$prestationIdentifiant];
                $prestation->setNbPassages($prestationParam['nbPassages']);

                $newContrat->addPrestation($prestation);
                $prestationPassage = clone $prestation;
                $prestationPassage->setNbPassages(null);
                $newPassage->addPrestation($prestationPassage);
            }
            $newPassage->setMouvementDeclenchable(true);
            $newPassage->addTechnicien($newContrat->getTechnicien());
            $newContrat->addPassage($etablissement, $newPassage);
            $newContrat->setStatut(ContratManager::STATUT_BROUILLON);
            $dm->persist($newPassage);
            $dm->flush();
            return $this->redirectToRoute('calendar', array('passage' => $newPassage->getId(),
                        'technicien' => $newContrat->getTechnicien()->getId(),
                        'calendrier' => $newContrat->getDateDebut()->format('Y-m-d')));
        }

        return $this->render('passage/creationRapide.html.twig', array('etablissement' => $etablissement, 'contrat' => $newContrat, 'form' => $form->createView()));
    }

    /**
     * @Route("/passage/deplanifier/{id}", name="passage_deplanifier")
     * @ParamConverter("passage", class="AppBundle:Passage")
     */
    public function deplanifierAction(Request $request, Passage $passage) {

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();

        if ($passage->isRealise()) {
            $aplanifier = false;
            $contrat = $passage->getContrat();
            foreach ($contrat->getPassages($passage->getEtablissement()) as $pass) {
                if ($pass->isAPlanifie() && $pass->getDateDebut() > $passage->getDateDebut()) {
                    $aplanifier = true;
                    $pass->setDateDebut(null);
                    $pass->setStatut(PassageManager::STATUT_EN_ATTENTE);
                    $dm->persist($passage);
                    $dm->flush();
                }
            }
            $passage->setDateFin(null);
            $passage->setDateRealise(null);
            if ($aplanifier) {
                $passage->setStatut(PassageManager::STATUT_A_PLANIFIER);
            } else {
                $pass->setDateDebut(null);
                $pass->setStatut(PassageManager::STATUT_EN_ATTENTE);
            }
            if ($contrat->isFini()) {
                $contrat->setStatut(ContratManager::STATUT_EN_COURS);
            }
            $dm->persist($passage);
            $dm->flush();
            return new Response(json_encode(array("success" => true)));
        }
        return new Response(json_encode(array("success" => false)));
    }

}

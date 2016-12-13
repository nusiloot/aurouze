<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Passage;
use AppBundle\Type\PassageMobileType;

class TourneeController extends Controller {

    /**
     * @Route("/tournees/{date}", name="tournees", defaults={"date" = "0"})
     */
    public function indexAction(Request $request, $date) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        if($date == "0"){
          $date = new \DateTime();
        }else{
          $date = \DateTime::createFromFormat('Y-m-d',$date);
        }
        $passageManager = $this->get('passage.manager');
        $passagesForAllTechniciens = $passageManager->getRepository()->findAllPassagesForTechnicien($date);
        $passagesByTechniciens = $passageManager->sortPassagesByTechnicien($passagesForAllTechniciens);
        return $this->render('tournee/index.html.twig', array('passagesByTechniciens' => $passagesByTechniciens, "date" => $date));
    }

    /**
     * @Route("/tournee-technicien/{technicien}/{date}", name="tournee_technicien", defaults={"date" = "0"})
     */
    public function tourneeTechnicienAction(Request $request,$technicien, $date) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        if($date == "0"){
          $date = new \DateTime();
        }else{
          $date = \DateTime::createFromFormat('Y-m-d',$date);
        }
        $technicien = $request->get('technicien');
        $technicienObj = null;
        if ($technicien) {
            $technicienObj = $dm->getRepository('AppBundle:Compte')->findOneById($technicien);
        }
        $passagesByTechnicien = $this->get('passage.manager')->getRepository()->findAllPassagesForTechnicien($date,$technicienObj);

        $historiqueAllPassages = array();
        $passagesForms = array();

        foreach ($passagesByTechnicien as $passage) {
          $historiqueAllPassages[$passage->getId()] = $this->get('contrat.manager')->getHistoriquePassagesByNumeroArchive($passage, 2);
          foreach ($historiqueAllPassages[$passage->getId()] as $hPassage) {
            $this->get('passage.manager')->synchroniseProduitsWithConfiguration($hPassage);
          }
          $passagesForms[$passage->getId()] = $this->createForm(new PassageMobileType($dm, $passage->getId()), $passage, array(
            'action' => $this->generateUrl('tournee_passage_rapport', array('passage' => $passage->getId(),'technicien' => $technicienObj->getId())),
            'method' => 'POST',
          ))->createView();

        }

        return $this->render('tournee/tourneeTechnicien.html.twig', array('passagesByTechnicien' => $passagesByTechnicien,
                                                                          "technicien" => $technicienObj,
                                                                          "date" => $date,
                                                                          "historiqueAllPassages" => $historiqueAllPassages,
                                                                          "passagesForms" => $passagesForms));
    }


    /**
     * @Route("/tournee-passage-rapport/{passage}/{technicien}", name="tournee_passage_rapport")
     * @ParamConverter("passage", class="AppBundle:Passage")
     */
    public function tourneePassageRapportAction(Request $request, Passage $passage) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $technicien = $request->get('technicien');
        $technicienObj = null;
        if ($technicien) {
            $technicienObj = $dm->getRepository('AppBundle:Compte')->findOneById($technicien);
        }

        $form = $this->createForm(new PassageMobileType($dm, $passage->getId()), $passage, array(
            'action' => $this->generateUrl('tournee_passage_rapport', array('passage' => $passage->getId(),'technicien' => $technicienObj->getId())),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {

            //return $this->render('passage/edition.html.twig', array('passage' => $passage, 'form' => $form->createView()));
        }
        $passageManager = $this->get('passage.manager');

        // $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($passage->getContrat()->getId());

        // if ($passage->getMouvementDeclenchable() && !$passage->getMouvementDeclenche()) {
        //     if ($contrat->generateMouvement($passage)) {
        //         $passage->setMouvementDeclenche(true);
        //     }
        // }

        $passage->setDateRealise($passage->getDateDebut());
        $dm->persist($passage);
        // $dm->persist($contrat);
        $dm->flush();

        // $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($passage->getContrat()->getId());
        // $contrat->verifyAndClose();
        //
        // $dm->flush();

        return $this->redirectToRoute('tournee_technicien', array('passage' => $passage->getId(),"technicien" => $technicienObj->getId()));
    }

    /**
     * @Route("/manifest/{technicien}/{date}", name="manifest")
     */
    public function manifestAction(Request $request) {
      $version = $request->get('version', null);
      $versionManifest = ($version)? $version : "1";

      $response = new Response();
      $response->setContent('CACHE MANIFEST');
      $response->headers->set('Content-Type', 'text/cache-manifest');

      return $this->render('tournee/manifest.twig', array('versionManifest' => $versionManifest),$response);
    }

}

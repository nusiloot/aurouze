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
     * @Route("/tournee-technicien/tournee/{technicien}/{date}", name="tournee_technicien", defaults={"date" = "0"})
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
        $pm = $this->get('passage.manager');
        $parameters = $pm->getParameters();
        if(!$parameters['coordonnees'] || !$parameters['coordonnees']['numero']){
          throw new Exception("Le paramétrage du numéro de téléphone n'est pas correct.");
        }
        $telephoneSecretariat = $parameters['coordonnees']['numero'];

        $rendezVousByTechnicien = $this->get('rendezvous.manager')->getRepository()->findByDateDebutAndParticipant($date->format('Y-m-d'),$technicienObj);

        $historiqueAllPassages = array();
        $passagesForms = array();

        $version = $this->getVersionManifest($technicienObj->getId(),$date->format('Y-m-d'));

        foreach ($rendezVousByTechnicien as $rendezVous) {
          if($passage = $rendezVous->getPassage()){
            $historiqueAllPassages[$passage->getId()] = $this->get('contrat.manager')->getHistoriquePassagesByNumeroArchive($passage, 2);
            foreach ($historiqueAllPassages[$passage->getId()] as $hPassage) {
              $this->get('passage.manager')->synchroniseProduitsWithConfiguration($hPassage);
            }
            $passagesForms[$passage->getId()] = $this->createForm(new PassageMobileType($dm, $passage->getId()), $passage, array(
              'action' => $this->generateUrl('tournee_passage_rapport', array('passage' => $passage->getId(),'technicien' => $technicienObj->getId())),
              'method' => 'POST',
              ))->createView();
          }
        }

        return $this->render('tournee/tourneeTechnicien.html.twig', array('rendezVousByTechnicien' => $rendezVousByTechnicien,
                                                                          "technicien" => $technicienObj,
                                                                          "date" => $date,
                                                                          "version" => $version,
                                                                          "historiqueAllPassages" => $historiqueAllPassages,
                                                                          'telephoneSecretariat' => $telephoneSecretariat,
                                                                          "passagesForms" => $passagesForms));
    }

    /**
     * @Route("/tournee-technicien/version/{technicien}", name="tournee_version")
     */
     public function tourneeVersionAction(Request $request,$technicien) {

         $dm = $this->get('doctrine_mongodb')->getManager();
         $date = $request->get('date',null);
         if(!$date){
           $dateTime = new \DateTime();
           $date = $dateTime->format('Y-m-d');
         }
         $technicien = $request->get('technicien');
         $technicienObj = null;
         if ($technicien) {
             $technicienObj = $dm->getRepository('AppBundle:Compte')->findOneById($technicien);
         }

         $version = $this->getVersionManifest($technicienObj->getId(),$date);

         return new Response(json_encode(array("success" => true,"version" => $version)));
     }

    /**
     * @Route("/tournee-technicien/passage-rapport/{passage}/{technicien}", name="tournee_passage_rapport")
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

        }
        $passageManager = $this->get('passage.manager');

        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($passage->getContrat()->getId());

        if ($passage->getMouvementDeclenchable() && !$passage->getMouvementDeclenche()) {
            if ($contrat->generateMouvement($passage)) {
                $passage->setMouvementDeclenche(true);
            }
        }

        $passage->setDateRealise($passage->getDateDebut());

        $passage->setSaisieTechnicien(true);
        if(!$passage->getPdfNonEnvoye()){
          $passage->setPdfNonEnvoye(true);
        }
        $dm->persist($passage);
        $dm->flush();

        $contrat = $dm->getRepository('AppBundle:Contrat')->findOneById($passage->getContrat()->getId());
        $contrat->verifyAndClose();

        $dm->flush();

        return $this->redirectToRoute('tournee_technicien', array("technicien" => $technicienObj->getId(), "date" => $passage->getDateDebut()->format("Y-m-d")));
    }

    /**
     * @Route("/tournee-technicien/manifest/{technicien}", name="manifest")
     */
    public function manifestAction(Request $request) {
      $dm = $this->get('doctrine_mongodb')->getManager();
      $version = $request->get('version', null);
      $date = $request->get('date', null);
      if(!$date){
        $dateTime = new \DateTime();
        $date = $dateTime->format('Y-m-d');
      }

      $technicien = $request->get('technicien');
      $technicienObj = null;
      if ($technicien) {
          $technicienObj = $dm->getRepository('AppBundle:Compte')->findOneById($technicien);
      }
      $versionManifest = ($version)? $version : $this->getVersionManifest($technicien,$date);

      $response = new Response();
      $response->headers->set('Content-Type', 'text/cache-manifest');

      return $this->render('tournee/manifest.twig', array('version' => $versionManifest),$response);
    }

    private function getVersionManifest($technicien,$date){
      return $this->get('passage.manager')->getRepository()->findLastDateModificationPassagesForTechnicien($technicien,$date);
    }

}

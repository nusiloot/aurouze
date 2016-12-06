<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Passage;

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
        return $this->render('tournee/journeeTechnicien.html.twig', array('passagesByTechnicien' => $passagesByTechnicien, "technicien" => $technicienObj, "date" => $date));
    }

    /**
     * @Route("/tournee-passage-visualisation/{passage}/{technicien}", name="tournee_passage_visualisation")
     * @ParamConverter("passage", class="AppBundle:Passage")
     */
    public function tourneePassageVisualisationAction(Request $request, Passage $passage) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $technicien = $request->get('technicien');
        $technicienObj = null;
        if ($technicien) {
            $technicienObj = $dm->getRepository('AppBundle:Compte')->findOneById($technicien);
        }
        $historiquePassages = $this->get('contrat.manager')->getHistoriquePassagesByNumeroArchive($passage, 2);
        foreach ($historiquePassages as $hPassage) {
          $this->get('passage.manager')->synchroniseProduitsWithConfiguration($hPassage);
        }
        return $this->render('tournee/passageVisualisation.html.twig', array('passage' => $passage, "technicien" => $technicienObj, "historiquePassages" => $historiquePassages));
    }



}

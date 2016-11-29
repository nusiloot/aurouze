<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $passageManager = $this->get('passage.manager');
        $passagesByTechnicien = $passageManager->getRepository()->findAllPassagesForTechnicien($date,$technicienObj);
        return $this->render('tournee/journeeTechnicien.html.twig', array('passagesByTechnicien' => $passagesByTechnicien, "technicien" => $technicienObj, "date" => $date));
    }



}

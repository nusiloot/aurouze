<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller {
	public static $colors = array(
			"#ABC8E2", // bleu
			"#FDD131", // jaune
			"#B0CC99", // vert
			"#EFA0FF", // violet
			"#C44C51", // rouge
			"#FF5B2B", // orange
			"#BF8273", // marron
            "#C0BFA9",   // moche    
            "#000000",   // noir 
			"#ed85d8",	// rose	
	);
    /**
     * @Route("/calendar", name="calendar")
     */
    public function calendarAction(Request $request) {
    	$dm = $this->get('doctrine_mongodb')->getManager();
    	
        $passage = $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantPassage($request->get('passage'));
        $technicien = $request->get('technicien');
        $techniciens = $dm->getRepository('AppBundle:Passage')->findTechniciens();
        
        
        return $this->render('calendar/calendar.html.twig', array('colors' => self::$colors, 'techniciens' => $techniciens, 'passage' => $passage, 'technicien' => $technicien));
    }

    /**
     * @Route("/calendar/update", name="calendarUpdate", options={"expose" = "true"})
     */
    public function calendarUpdateAction(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $error = false;
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $passageToMove = $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantPassage($request->get('id'));

        $start = $request->get('start');
        $end = $request->get('end');

        if ($error) {
            throw new \Exception();
        }

        $event = array('id' => $passageToMove->getPassageIdentifiant(),
            'title' => $passageToMove->getIntitule(),
            'start' => $start,
            'end' => $end, 
        	'backgroundColor' => (isset(self::$colors[$passageToMove->getTechnicien()])) ? self::$colors[$passageToMove->getTechnicien()] : "yellow", 
        	'textColor' => "black"
        );

        $passageToMove->setDateDebut($start);
        $passageToMove->setDateFin($end);
        $dm->persist($passageToMove);
        $dm->flush();

        $response = new Response(json_encode($event));
        
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/calendar/populate", name="calendarPopulate", options={"expose" = "true"})
     */
    public function calendarPopulateAction(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $periodeStart = $request->get('start');
        $periodeEnd = $request->get('end');
        $passagesTech = ($request->get('technicien'))?
        	$dm->getRepository('AppBundle:Passage')->findAllByPeriodeAndIdentifiantTechnicien($periodeStart, $periodeEnd, $request->get('technicien')) :
        	$dm->getRepository('AppBundle:Passage')->findAllByPeriode($periodeStart, $periodeEnd);

        $passagesCalendar = array();

        foreach ($passagesTech as $passageTech) {
        	if (!$passageTech->getDateFin()) {
        		continue;
        	}
            $passageArr = array('id' => $passageTech->getPassageIdentifiant(),
                'title' => $passageTech->getPassageEtablissement()->getIntitule(),
                'start' => $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $passageTech->getDateFin()->format('Y-m-d\TH:i:s'), 
            	'backgroundColor' => (isset(self::$colors[$passageTech->getTechnicien()])) ? self::$colors[$passageTech->getTechnicien()] : "yellow",
            	'textColor' => "black");
            $passagesCalendar[] = $passageArr;
        }
        $response = new Response(json_encode($passagesCalendar));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/calendar/read", name="calendarRead", options={"expose" = "true"})
     */
    public function calendarReadAction(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $error = false;
        $dm = $this->get('doctrine_mongodb')->getManager();
        $passage = $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantPassage($request->get('id'));
        

        if ($error) {
            throw new \Exception();
        }

        return $this->render('calendar/calendarModal.html.twig', array('passage' => $passage));
    }

    /**
     * @Route("/calendar/delete", name="calendarDelete", options={"expose" = "true"})
     */
    public function calendarDeleteAction(Request $request) {

        $error = false;

        $dm = $this->get('doctrine_mongodb')->getManager();
        $passageToDelete = $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantPassage($request->get('passage'));
        $technicien = $passageToDelete->getTechnicien();

        $passageToDelete->setDateFin(null);
        $dm->persist($passageToDelete);
        $dm->flush();
        if ($error) {
            throw new \Exception();
        }

        return $this->redirect($this->generateUrl('calendar', array('passage' => $request->get('passage'), 'technicien' => $technicien)));
    }

}

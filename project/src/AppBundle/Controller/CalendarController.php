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
     * @Route("/calendar/manuel", name="calendarManuel")
     */
    public function calendarManuelAction(Request $request) {
    	$dm = $this->get('doctrine_mongodb')->getManager();
    	
    	$periodeStart = '2016-01-11';
    	$periodeEnd = '2016-01-17'; 
    	
		$passagesTech = $dm->getRepository('AppBundle:Passage')->findAllByPeriode($periodeStart, $periodeEnd);
    	
    	$eventsDates = array();
    	$techniciens = array();
    	
		while (strtotime($periodeStart) < strtotime($periodeEnd)) {
			$eventsDates[$periodeStart] = array();
			$periodeStart = date ("Y-m-d", strtotime("+1 day", strtotime($periodeStart)));
		}
    	
    	$passagesCalendar = array();
    	$index = 0;
    	$indexColor = 0;
    	foreach ($passagesTech as $passageTech) {
    		if (!$passageTech->getDateFin()) {
    			continue;
    		}
    		$techniciens[$passageTech->getTechnicien()] = $passageTech->getTechnicien();

    		if (!isset($passagesCalendar[$passageTech->getTechnicien()])) {
    			$passagesCalendar[$passageTech->getTechnicien()] = array();
    			$index = 0;
    			$indexColor++;
    		} 
    		
    		if (isset($passagesCalendar[$passageTech->getTechnicien()]) && isset($passagesCalendar[$passageTech->getTechnicien()][($index - 1)]) && $passagesCalendar[$passageTech->getTechnicien()][($index - 1)]['end'] >= $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'))
    		{
    			$passagesCalendar[$passageTech->getTechnicien()][($index - 1)]['end'] = $passageTech->getDateFin()->format('Y-m-d\TH:i:s');
    			$diffFin = (strtotime($passageTech->getDateFin()->format('Y-m-d H:i:s')) - strtotime($passageTech->getDateDebut()->format('Y-m-d').' 06:00:00')) / 60;
    			$passagesCalendar[$passageTech->getTechnicien()][($index - 1)]['coefEnd'] = round($diffFin / 30, 2);
    			continue;
    		}


    		$dateDebut = new \DateTime($passageTech->getDateDebut()->format('Y-m-d').'T06:00:00');
    		$diffDebut = (strtotime($passageTech->getDateDebut()->format('Y-m-d H:i:s')) - strtotime($passageTech->getDateDebut()->format('Y-m-d').' 06:00:00')) / 60;
    		$diffFin = (strtotime($passageTech->getDateFin()->format('Y-m-d H:i:s')) - strtotime($passageTech->getDateDebut()->format('Y-m-d').' 06:00:00')) / 60;
    		
    		$passageArr = array(
    				'start' => $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'),
    				'end' => $passageTech->getDateFin()->format('Y-m-d\TH:i:s'),
    				'backgroundColor' => (isset(self::$colors[$indexColor]))? self::$colors[$indexColor] : 'yellow',
    				'textColor' => "black",
    				'coefStart' => round($diffDebut / 30, 1),
    				'coefEnd' => round($diffFin / 30, 2),
    		);
    		$index++;
    		
    		$passagesCalendar[$passageTech->getTechnicien()][] = $passageArr;
    	}
    	
    	
    	foreach ($eventsDates as $date => $value) {
    		foreach ($techniciens as $technicien) {
    			$eventsDates[$date][$technicien] = array();
    			if (isset($passagesCalendar[$technicien])) {
    				foreach ($passagesCalendar[$technicien] as $passage) {
    					if (preg_match("/^$date/", $passage['start'])) {
    						$eventsDates[$date][$technicien][] = $passage;
    					}
    				}
    			}
    		}
    	}
        return $this->render('calendar/calendarManuel.html.twig', array('eventsDates' => $eventsDates, 'nbTechniciens' => count($techniciens)));
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
        $id = ($request->get('passage'))? $request->get('passage') : $request->get('id');
        $technicien =  $request->get('technicien');
        
        $passageToMove = $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantPassage($id);

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
		if ($technicien) {
        	$passageToMove->setTechnicien($technicien);
		}
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
                'title' => ($request->get('title'))? $passageTech->getPassageEtablissement()->getIntitule() : "",
                'start' => $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $passageTech->getDateFin()->format('Y-m-d\TH:i:s'), 
            	'backgroundColor' => $this->getColor($passageTech->getTechnicien()),
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
        $technicien =  $request->get('technicien');
        

        if ($error) {
            throw new \Exception();
        }

        return $this->render('calendar/calendarModal.html.twig', array('passage' => $passage, 'technicien' => $technicien, 'light' => $request->get('light')));
    }

    /**
     * @Route("/calendar/delete", name="calendarDelete", options={"expose" = "true"})
     */
    public function calendarDeleteAction(Request $request) {

        $error = false;

        $dm = $this->get('doctrine_mongodb')->getManager();
        $passageToDelete = $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantPassage($request->get('passage'));
        $technicien =  $request->get('technicien');

        $passageToDelete->setDateFin(null);
        $dm->persist($passageToDelete);
        $dm->flush();
        if ($error) {
            throw new \Exception();
        }

        return $this->redirect($this->generateUrl('calendar', array('passage' => $request->get('passage'), 'technicien' => $technicien)));
    }
    

	/*
	 * Fonction temporaire
	 */
    public function getColor($technicien) {
    	$dm = $this->get('doctrine_mongodb')->getManager();
    	$techniciens = $dm->getRepository('AppBundle:Passage')->findTechniciens();
    	$index = 0;
    	$find = false;
    	foreach ($techniciens as $tech) {
    		if ($tech == $technicien) {
    			$find = true;
    			break;
    		}
    		$index++;
    	}
    	return ($find)? self::$colors[$index] : 'yellow';
    }

}

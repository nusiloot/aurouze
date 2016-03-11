<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Tool\CalendarDateTool;
use AppBundle\Document\User;

class CalendarController extends Controller {
    /**
     * @Route("/calendar", name="calendar")
     */
    public function calendarAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $passage = $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantPassage($request->get('passage'));
        $technicien = $request->get('technicien');
        $techniciens = $dm->getRepository('AppBundle:User')->findAllByType(User::USER_TYPE_TECHNICIEN);
        
        $calendrier = $request->get('calendrier');
        $calendarTool = new CalendarDateTool($calendrier);
        
        return $this->render('calendar/calendar.html.twig', array('calendarTool' => $calendarTool, 'techniciens' => $techniciens, 'passage' => $passage, 'technicien' => $technicien));
    }
    /**
     * @Route("/calendar/global", name="calendarManuel")
     */
    public function calendarManuelAction(Request $request) {
    	$dm = $this->get('doctrine_mongodb')->getManager();
        $passage = $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantPassage($request->get('passage'));
        
        $calendrier = $request->get('calendrier');
        $calendarTool = new CalendarDateTool($calendrier);
    	
    	$periodeStart = $calendarTool->getDateDebutSemaine('Y-m-d');
    	$periodeEnd = $calendarTool->getDateFinSemaine('Y-m-d');
    	
		$passagesTech = $dm->getRepository('AppBundle:Passage')->findAllByPeriode($periodeStart, $periodeEnd);
    	
    	$eventsDates = array();
    	$techniciens = $dm->getRepository('AppBundle:User')->findAllByType(User::USER_TYPE_TECHNICIEN);
    	
		while (strtotime($periodeStart) < strtotime($periodeEnd)) {
			$eventsDates[$periodeStart] = array();
			$periodeStart = date ("Y-m-d", strtotime("+1 day", strtotime($periodeStart)));
		}
    	
    	$passagesCalendar = array();
    	$index = 0;
    	foreach ($passagesTech as $passageTech) {
    		if (!$passageTech->getDateFin()) {
    			continue;
    		}

    		if (!isset($passagesCalendar[$passageTech->getTechnicien()])) {
    			$passagesCalendar[$passageTech->getTechnicien()] = array();
    			$index = 0;
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
    		$tech = $dm->getRepository('AppBundle:User')->findByIdentite($passageTech->getTechnicien());
    		$passageArr = array(
    				'start' => $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'),
    				'end' => $passageTech->getDateFin()->format('Y-m-d\TH:i:s'),
    				'backgroundColor' => ($tech)? $tech->getCouleur() : User::COULEUR_DEFAUT,
    				'textColor' => "black",
    				'coefStart' => round($diffDebut / 30, 1),
    				'coefEnd' => round($diffFin / 30, 2),
    		);
    		$index++;
    		
    		$passagesCalendar[$passageTech->getTechnicien()][] = $passageArr;
    	}
    	
    	foreach ($eventsDates as $date => $value) {
    		foreach ($techniciens as $technicien) {
    			$eventsDates[$date][$technicien->getIdentite()] = array();
    			if (isset($passagesCalendar[$technicien->getIdentite()])) {
    				foreach ($passagesCalendar[$technicien->getIdentite()] as $p) {
    					if (preg_match("/^$date/", $p['start'])) {
    						$eventsDates[$date][$technicien->getIdentite()][] = $p;
    					}
    				}
    			}
    		}
    	}
        return $this->render('calendar/calendarManuel.html.twig', array('calendarTool' => $calendarTool, 'eventsDates' => $eventsDates, 'nbTechniciens' => count($techniciens), 'techniciens' => $techniciens, 'technicien' => null, 'passage' => $passage));
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
        $tech = $dm->getRepository('AppBundle:User')->findByIdentite($technicien);
        $event = array('id' => $passageToMove->getPassageIdentifiant(),
            'title' => $passageToMove->getIntitule(),
            'start' => $start,
            'end' => $end, 
        	'backgroundColor' => ($tech)? $tech->getCouleur() : User::COULEUR_DEFAUT,
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
        	$tech = $dm->getRepository('AppBundle:User')->findByIdentite($passageTech->getTechnicien());
            $passageArr = array('id' => $passageTech->getPassageIdentifiant(),
                'title' => ($request->get('title'))? $passageTech->getEtablissementInfos()->getIntitule() : "",
                'start' => $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $passageTech->getDateFin()->format('Y-m-d\TH:i:s'), 
            	'backgroundColor' => ($tech)? $tech->getCouleur() : User::COULEUR_DEFAUT,
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

}

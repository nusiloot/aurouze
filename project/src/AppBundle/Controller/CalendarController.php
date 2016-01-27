<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller {
    
    /**
     * @Route("/calendar/{etablissement}", name="calendar")
     */
    public function calendarAction(Request $request) {
    	
    	$etablissement = $request->get('etablissement');
    	
    	return $this->render('default/calendar.html.twig', array('etablissement' => $etablissement));
    }
    
    /**
     * @Route("/calendar/{etablissement}/update", name="calendarUpdate", options={"expose" = "true"})
     */
    public function calendarUpdateAction(Request $request) {
    	
    	if (!$request->isXmlHttpRequest()) {
    		throw $this->createNotFoundException();
    	}
    	
    	$error = false;
    	
    	$request->get('etablissement');
    	$id = $request->get('id');
    	$start = $request->get('start');
    	$end = $request->get('end');
    	$duration = "2";
    	if (!$end) {
    		$end = new \DateTime($start);
    		$end->modify("+$duration hour");
    		$end = str_replace($end->format('P'), '', $end->format('c'));
    	}
    	
    	if ($error) {
    		throw new \Exception();
    	}

    	$event = array('id' => "E-001-P-001", 'title' => "Martial // Le petit opportun", 'start' => $start, 'end' => $end, 'backgroundColor' => "blue", 'textColor' => "white");
    	
    	$response = new Response(json_encode($event));
    	$response->headers->set('Content-Type', 'application/json');
    	return $response;
    }
    
    /**
     * @Route("/calendar/{etablissement}/populate", name="calendarPopulate", options={"expose" = "true"})
     */
    public function calendarPopulateAction(Request $request) {
    	
    	if (!$request->isXmlHttpRequest()) {
    		throw $this->createNotFoundException();
    	}
    	
    	$periodeStart = $request->get('start');
    	$periodeEnd = $request->get('end');
    	
    	$events = array(
    			array('id' => "E-001-P-000", 'title' => "McDo // Rue Berger", 'start' => "2016-01-28T11:00:00", 'end' => "2016-01-28T13:00:00", 'backgroundColor' => "yellow", 'textColor' => "black")
    	);
    	
    	$response = new Response(json_encode($events));
    	$response->headers->set('Content-Type', 'application/json');
    	return $response;
    }
    
    /**
     * @Route("/calendar/{etablissement}/delete", name="calendarDelete", options={"expose" = "true"})
     */
    public function calendarDeleteAction(Request $request) {
    	
    	if (!$request->isXmlHttpRequest()) {
    		throw $this->createNotFoundException();
    	}
    	
    	$error = false;
    	
    	$request->get('etablissement');
    	$request->get('id');
    	
    	if ($error) {
    		throw new \Exception();
    	}
    	
    	$response = new Response(json_encode(array('id' => "E-001-P-001")));
    	$response->headers->set('Content-Type', 'application/json');
    	return $response;
    }

}

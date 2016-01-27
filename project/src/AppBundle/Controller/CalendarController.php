<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller {
    
    /**
     * @Route("/calendar", name="calendar")
     */
    public function calendarAction(Request $request) {
    	
    
    	return $this->render('default/calendar.html.twig');
    }
    
    /**
     * @Route("/calendar/{identifiantEtablissement}/update", name="calendarUpdate", options={"expose" = "true"})
     */
    public function calendarUpdateAction(Request $request) {
    	
    	if (!$request->isXmlHttpRequest()) {
    		throw $this->createNotFoundException();
    	}
    	
    	$error = false;
    	
    	/*var_dump($request->get('identifiantEtablissement'));
    	var_dump($request->get('id'));
    	var_dump($request->get('start'));
    	var_dump($request->get('end'));*/
    	
    	$response = ($error)? new Response(json_encode(array('id' => null, 'error' => 1))) : new Response(json_encode(array('id' => "E-001-P-001", 'error' => 0)));
    	$response->headers->set('Content-Type', 'application/json');
    	return $response;
    }
    
    /**
     * @Route("/calendar/{identifiantEtablissement}/populate", name="calendarPopulate", options={"expose" = "true"})
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

}

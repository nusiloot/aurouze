<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller {

    /**
     * @Route("/calendar/{etablissement}/{passage}/{technicien}", name="calendar")
     */
    public function calendarAction(Request $request) {
    	
    	$etablissement = $request->get('etablissement');
        $passage = $request->get('passage');
        $technicien = ($request->get('technicien_choice'))? $request->get('technicien_choice')['technicien'] : $request->get('technicien');
        $technicienForm = $this->get('technicien.choix');
        
        
        $form = $this->createForm($technicienForm, null,array(
        		'action' => $this->generateUrl('calendar', array('etablissement' => $etablissement, 'passage' => $passage, 'technicien' => $technicien)),
        		'method' => 'GET',
        ));
        
        
        return $this->render('calendar/calendar.html.twig', array('etablissement' => $etablissement, 'passage' => $passage, 'technicien' => $technicien, 'form' => $form->createView()));
    }

    /**
     * @Route("/calendar/{etablissement}/{passage}/{technicien}/update", name="calendarUpdate", options={"expose" = "true"})
     */
    public function calendarUpdateAction(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $error = false;
        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissement = $dm->getRepository('AppBundle:Etablissement')->findOneByIdentifiant($request->get('etablissement'));
        $request->get('etablissement');

        $id = $request->get('id');
        $passageToMove = (!$id)?
                $dm->getRepository('AppBundle:Passage')->findOneByIdentifiantEtablissementAndIdentifiantPassage($request->get('etablissement'), $request->get('passage')) 
                : $dm->getRepository('AppBundle:Passage')->findOneById($request->get('id'));

        $id = $passageToMove->getId();

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

        $event = array('id' => $id,
            'title' => $passageToMove->getPassageEtablissement()->getNom() . ' ' . $passageToMove->getPassageEtablissement()->getAdressecomplete(),
            'start' => $start,
            'end' => $end, 'backgroundColor' => "yellow", 'textColor' => "black");

        $passageToMove->setDateDebut($start);
        $passageToMove->setDateFin($end);
        $dm->persist($passageToMove);
        $dm->flush();

        $response = new Response(json_encode($event));
        
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/calendar/{etablissement}/{passage}/{technicien}/populate", name="calendarPopulate", options={"expose" = "true"})
     */
    public function calendarPopulateAction(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $dm = $this->get('doctrine_mongodb')->getManager();
        $periodeStart = $request->get('start');
        $periodeEnd = $request->get('end');
        $etablissement = $dm->getRepository('AppBundle:Etablissement')->findOneByIdentifiant($request->get('etablissement'));
        $passagesTech = $dm->getRepository('AppBundle:Passage')->findAllByPeriodeAndIdentifiantTechnicien($periodeStart, $periodeEnd, $request->get('technicien'));

        $passagesCalendar = array();

        foreach ($passagesTech as $passageTech) {
        	if (!$passageTech->getDateFin()) {
        		continue;
        	}
            $passageArr = array('id' => $passageTech->getId(),
                'title' => $passageTech->getPassageEtablissement()->getNom() . ' ' . $passageTech->getPassageEtablissement()->getAdressecomplete(),
                'start' => $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $passageTech->getDateFin()->format('Y-m-d\TH:i:s'), 'backgroundColor' => "yellow", 'textColor' => "black");
            $passagesCalendar[] = $passageArr;
        }
        $response = new Response(json_encode($passagesCalendar));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/calendar/{etablissement}/{passage}/{technicien}/read", name="calendarRead", options={"expose" = "true"})
     */
    public function calendarReadAction(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $error = false;

        $etablissement = $request->get('etablissement');
        $passage = $request->get('passage');
        $technicien = $request->get('technicien');
        $id = $request->get('id');

        if ($error) {
            throw new \Exception();
        }

        return $this->render('calendar/calendarModal.html.twig', array('etablissement' => $etablissement, 'passage' => $passage, 'technicien' => $technicien, 'id' => $id));
    }

    /**
     * @Route("/calendar/{etablissement}/{passage}/{technicien}/{id}/delete", name="calendarDelete", options={"expose" = "true"})
     */
    public function calendarDeleteAction(Request $request) {

        $error = false;

        $etablissement = $request->get('etablissement');
        $passage = $request->get('passage');
        $technicien = $request->get('technicien');
        $dm = $this->get('doctrine_mongodb')->getManager();
        $passageToDelete = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('id'));

        $passageToDelete->setDateDebut(null);
        $passageToDelete->setDateFin(null);
        $dm->persist($passageToDelete);
        $dm->flush();
        if ($error) {
            throw new \Exception();
        }

        return $this->redirect($this->generateUrl('calendar', array('etablissement' => $etablissement, 'passage' => $passage, 'technicien' => "1")));
    }

}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Tool\CalendarDateTool;
use AppBundle\Document\Compte;
use AppBundle\Document\CompteInfos;
use Behat\Transliterator\Transliterator;
use AppBundle\Type\PassageCreationType;

class CalendarController extends Controller {

    /**
     * @Route("/calendrier", name="calendar")
     */
    public function calendarAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $passage = null;
        if($request->get('passage')) {
            $passage = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('passage'));
        }
        
        $technicien = $request->get('technicien');

        $techniciens = $dm->getRepository('AppBundle:Compte')->findAllActif();

        $calendrier = $request->get('calendrier');
        $calendarTool = new CalendarDateTool($calendrier);
        $etablissement = null;
        if($passage) {
		    $etablissement = $passage->getEtablissement();
        }
        return $this->render('calendar/calendar.html.twig', array('calendarTool' => $calendarTool, 'techniciens' => $techniciens, 'passage' => $passage, 'technicien' => $technicien, 'etablissement' => $etablissement));
    }

    /**
     * @Route("/calendrier/global", name="calendarManuel")
     */
    public function calendarManuelAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $passage = null;
        if($request->get('passage')) {
            $passage = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('passage'));
        }

        $calendrier = $request->get('calendrier');
        $calendarTool = new CalendarDateTool($calendrier);

        $periodeStart = $calendarTool->getDateDebutSemaine('Y-m-d');
        $periodeEnd = $calendarTool->getDateFinSemaine('Y-m-d');

        $passagesTech = $dm->getRepository('AppBundle:Passage')->findAllByPeriode($periodeStart, $periodeEnd);

        $eventsDates = array();

        $techniciens = $dm->getRepository('AppBundle:Compte')->findAllActif();

        while (strtotime($periodeStart) < strtotime($periodeEnd)) {
            $eventsDates[$periodeStart] = array();
            $periodeStart = date("Y-m-d", strtotime("+1 day", strtotime($periodeStart)));
        }

        $passagesCalendar = array();
        $index = 0;
        foreach ($passagesTech as $passageTech) {
        	foreach ($passageTech->getTechniciens() as $technicien) {
	            if (!$passageTech->getDateFin()) {
	                continue;
	            }

	            if (!isset($passagesCalendar[$technicien->getIdentifiant()])) {
	                $passagesCalendar[$technicien->getIdentifiant()] = array();
	                $index = 0;
	            }

	            if (isset($passagesCalendar[$technicien->getIdentifiant()]) && isset($passagesCalendar[$technicien->getIdentifiant()][($index - 1)]) && $passagesCalendar[$technicien->getIdentifiant()][($index - 1)]['end'] >= $passageTech->getDateDebut()->format('Y-m-d\TH:i:s')) {
	                $passagesCalendar[$technicien->getIdentifiant()][($index - 1)]['end'] = $passageTech->getDateFin()->format('Y-m-d\TH:i:s');
	                $diffFin = (strtotime($passageTech->getDateFin()->format('Y-m-d H:i:s')) - strtotime($passageTech->getDateDebut()->format('Y-m-d') . ' 06:00:00')) / 60;
	                $passagesCalendar[$technicien->getIdentifiant()][($index - 1)]['coefEnd'] = round($diffFin / 30, 2);
	                continue;
	            }


	            $dateDebut = new \DateTime($passageTech->getDateDebut()->format('Y-m-d') . 'T06:00:00');
	            $diffDebut = (strtotime($passageTech->getDateDebut()->format('Y-m-d H:i:s')) - strtotime($passageTech->getDateDebut()->format('Y-m-d') . ' 06:00:00')) / 60;
	            $diffFin = (strtotime($passageTech->getDateFin()->format('Y-m-d H:i:s')) - strtotime($passageTech->getDateDebut()->format('Y-m-d') . ' 06:00:00')) / 60;

                $tech = $dm->getRepository('AppBundle:Compte')->findOneById($technicien->getId());

	            $passageArr = array(
	                'start' => $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'),
	                'end' => $passageTech->getDateFin()->format('Y-m-d\TH:i:s'),
	                'backgroundColor' => ($tech) ? $tech->getCouleur() : Compte::COULEUR_DEFAUT,
	                'textColor' => "black",
	                'coefStart' => round($diffDebut / 30, 1),
	                'coefEnd' => round($diffFin / 30, 2),
	            );
	            $index++;

	            $passagesCalendar[$technicien->getIdentifiant()][] = $passageArr;
        	}
        }

        foreach ($eventsDates as $date => $value) {
            foreach ($techniciens as $technicien) {
                $eventsDates[$date][$technicien->getIdentifiant()] = array();
                if (isset($passagesCalendar[$technicien->getIdentifiant()])) {
                    foreach ($passagesCalendar[$technicien->getIdentifiant()] as $p) {
                        if (preg_match("/^$date/", $p['start'])) {
                            $eventsDates[$date][$technicien->getIdentifiant()][] = $p;
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
        $id = ($request->get('passage')) ? $request->get('passage') : $request->get('id');
        $technicien = $request->get('technicien');

        $passageToMove = $dm->getRepository('AppBundle:Passage')->findOneById($id);

        $start = $request->get('start');
        $end = $request->get('end');

        if ($error) {
            throw new \Exception();
        }
        $tech = $dm->getRepository('AppBundle:Compte')->findOneById($technicien);
        $event = array('id' => $passageToMove->getId(),
            'title' => $passageToMove->getIntitule(),
            'start' => $start,
            'end' => $end,
            'backgroundColor' => ($tech) ? $tech->getCouleur() : Compte::COULEUR_DEFAUT,
            'textColor' => "black"
        );
        if ($tech) {
            $compteInfos = new CompteInfos();
            $compteInfos->copyFromCompte($tech);
            $passageToMove->addTechnicien($tech);
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
        $technicien = $dm->getRepository('AppBundle:Compte')->findOneById($request->get('technicien'));
        $periodeStart = $request->get('start');
        $periodeEnd = $request->get('end');
        $passagesTech = $dm->getRepository('AppBundle:Passage')->findAllByPeriodeAndIdentifiantTechnicien($periodeStart, $periodeEnd, $technicien);

        $passagesCalendar = array();

        foreach ($passagesTech as $passageTech) {
            if (!$passageTech->getDateFin()) {
                continue;
            }
           $passageArr = array('id' => $passageTech->getId(),
                'title' => ($request->get('title')) ? $passageTech->getEtablissementInfos()->getIntitule() : "",
                'start' => $passageTech->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $passageTech->getDateFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $technicien->getCouleur(),
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

        $dm = $this->get('doctrine_mongodb')->getManager();
        $passage = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('id'));
        $technicien = $request->get('technicien');

        $form = $this->createForm(new PassageCreationType($dm), $passage, array(
        		'action' => $this->generateUrl('calendarRead', array('id' => $request->get('id'), 'technicien' => $request->get('technicien'))),
        		'method' => 'POST',
        		'attr' => array('id' => 'eventForm')
        ));
        if (!$passage->isRealise()) {
	        $form->handleRequest($request);
	        if ($form->isSubmitted() && $form->isValid()) {
	        	$passage = $form->getData();
	        	$dm->persist($passage);
	        	$dm->flush();
	        	return new Response(json_encode(array("success" => true)));
	        }
        }
        return $this->render('calendar/calendarModal.html.twig', array('form' => $form->createView(), 'passage' => $passage, 'technicien' => $technicien, 'light' => $request->get('light')));
    }

    /**
     * @Route("/calendar/delete", name="calendarDelete", options={"expose" = "true"})
     */
    public function calendarDeleteAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $passageToDelete = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('passage'));
        $technicien = $request->get('technicien');

        if (!$passageToDelete->isRealise()) {
        	$passageToDelete->setDateFin(null);
        	$dm->persist($passageToDelete);
        	$dm->flush();
        }

        return $this->redirect($this->generateUrl('calendar', array('passage' => $request->get('passage'), 'technicien' => $technicien)));
    }

}

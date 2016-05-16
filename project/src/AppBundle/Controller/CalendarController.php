<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Tool\CalendarDateTool;
use AppBundle\Document\Compte;
use AppBundle\Document\RendezVous;
use AppBundle\Document\CompteInfos;
use Behat\Transliterator\Transliterator;
use AppBundle\Type\PassageCreationType;
use AppBundle\Type\RendezVousType;

class CalendarController extends Controller {

    /**
     * @Route("/calendrier", name="calendar")
     */
    public function calendarAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $passage = null;
        if ($request->get('passage')) {
            $passage = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('passage'));
        }

        $technicien = $request->get('technicien');
        $technicienObj = null;
        if ($technicien) {
            $technicienObj = $dm->getRepository('AppBundle:Compte')->findOneById($technicien);
        }
        $techniciens = $dm->getRepository('AppBundle:Compte')->findAllUtilisateursCalendrier();

        $date = $request->get('date', new \DateTime());
        $calendarTool = new CalendarDateTool($date, $request->get('mode', CalendarDateTool::MODE_WEEK));

        $etablissement = null;
        if ($passage) {
            $etablissement = $passage->getEtablissement();
        }

        return $this->render('calendar/calendar.html.twig', array('calendarTool' => $calendarTool, 'techniciens' => $techniciens, 'passage' => $passage, 'technicien' => $technicien, 'technicienObj' => $technicienObj, 'etablissement' => $etablissement, 'date' => $date, 'mode' => $request->get('mode')));
    }

    /**
     * @Route("/calendrier/global", name="calendarManuel")
     */
    public function calendarManuelAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $passage = null;
        if ($request->get('passage')) {
            $passage = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('passage'));
        }

        $calendarTool = new CalendarDateTool($request->get('date'), $request->get('mode'));

        $periodeStart = $calendarTool->getDateDebutSemaine('Y-m-d');
        $periodeEnd = $calendarTool->getDateFinSemaine('Y-m-d');

        $rdvs = $dm->getRepository('AppBundle:RendezVous')->findByDate($periodeStart, $periodeEnd);

        $eventsDates = array();
        while (strtotime($periodeStart) < strtotime($periodeEnd)) {
            $eventsDates[$periodeStart] = array();
            $periodeStart = date("Y-m-d", strtotime("+1 day", strtotime($periodeStart)));
        }

        $techniciens = $dm->getRepository('AppBundle:Compte')->findAllUtilisateursCalendrier();

        $passagesCalendar = array();
        $index = 0;
        foreach ($rdvs as $rdv) {
            foreach ($rdv->getParticipants() as $compte) {
                if (!$rdv->getDateFin()) {
                    continue;
                }

                if (!isset($passagesCalendar[$compte->getIdentifiant()])) {
                    $passagesCalendar[$compte->getIdentifiant()] = array();
                    $index = 0;
                }

                if (isset($passagesCalendar[$compte->getIdentifiant()]) && isset($passagesCalendar[$compte->getIdentifiant()][($index - 1)]) && $passagesCalendar[$compte->getIdentifiant()][($index - 1)]['end'] >= $rdv->getDateDebut()->format('Y-m-d\TH:i:s')) {
                    $passagesCalendar[$compte->getIdentifiant()][($index - 1)]['end'] = $rdv->getDateFin()->format('Y-m-d\TH:i:s');
                    $diffFin = (strtotime($rdv->getDateFin()->format('Y-m-d H:i:s')) - strtotime($rdv->getDateDebut()->format('Y-m-d') . ' 06:00:00')) / 60;
                    $passagesCalendar[$compte->getIdentifiant()][($index - 1)]['coefEnd'] = round($diffFin / 30, 2);
                    continue;
                }


                $dateDebut = new \DateTime($rdv->getDateDebut()->format('Y-m-d') . 'T06:00:00');
                $diffDebut = (strtotime($rdv->getDateDebut()->format('Y-m-d H:i:s')) - strtotime($rdv->getDateDebut()->format('Y-m-d') . ' 06:00:00')) / 60;
                $diffFin = (strtotime($rdv->getDateFin()->format('Y-m-d H:i:s')) - strtotime($rdv->getDateDebut()->format('Y-m-d') . ' 06:00:00')) / 60;

                $passageArr = array(
                    'start' => $rdv->getDateDebut()->format('Y-m-d\TH:i:s'),
                    'end' => $rdv->getDateFin()->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $compte->getCouleur(),
                    'textColor' => $rdv->getTextColor(),
                    'coefStart' => round($diffDebut / 30, 1),
                    'coefEnd' => round($diffFin / 30, 2),
                    'resume' => $rdv->getTitre(),
                );
                $index++;

                $passagesCalendar[$compte->getIdentifiant()][] = $passageArr;
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
        return $this->render('calendar/calendarManuel.html.twig', array('calendarTool' => $calendarTool, 'eventsDates' => $eventsDates, 'nbTechniciens' => count($techniciens), 'techniciens' => $techniciens, 'technicien' => null, 'passage' => $passage, 'date' => $request->get('date')));
    }

    /**
     * @Route("/calendar/add/passage", name="calendarAdd", options={"expose" = "true"})
     */
    public function calendarAddAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $pm = $this->get('passage.manager');
        $rvm = $this->get('rendezvous.manager');

        $passage = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('passage'));

        $technicien = $dm->getRepository('AppBundle:Compte')->findOneById($request->get('technicien'));
        $rdv = $rvm->createFromPassage($passage);

        $rdv->setDateDebut(new \DateTime($request->get('start')));
        $rdv->setDateFin(new \DateTime($request->get('end')));
        $rdv->removeAllParticipants();
        $rdv->addParticipant($technicien);

        $dm->persist($rdv);
        $pm->updateNextPassageAPlannifier($rdv->getPassage());
        $dm->flush();

        $response = new Response(json_encode($rdv->getEventJson($technicien->getCouleur())));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/calendar/add/libre", name="calendarAddLibre", options={"expose" = "true"})
     */
    public function calendarAddLibreAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $rdv = new RendezVous();
        $rdv->setDateDebut(new \DateTime($request->get('start')));
        $dateFin = clone $rdv->getDateDebut();
        $dateFin = $dateFin->modify("+1 hour");
        $rdv->setDateFin($dateFin);
        if($request->get('technicien')) {
            $rdv->addParticipant($dm->getRepository('AppBundle:Compte')->findOneById($request->get('technicien')));
        }

        $form = $this->createForm(new RendezVousType($dm), $rdv, array(
            'action' => $this->generateUrl('calendarAddLibre'),
            'method' => 'POST',
            'attr' => array('id' => 'eventForm'),
            'rdv_libre' => true
        ));

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            return $this->render('calendar/rendezVous.html.twig', array('rdv' => $rdv, 'form' => $form->createView()));
        }

        $dm->persist($rdv);

        $dm->flush();

        return new Response(json_encode(array("success" => true)));
    }

    /**
     * @Route("/calendar/update", name="calendarUpdate", options={"expose" = "true"})
     */
    public function calendarUpdateAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $rvm = $this->get('rendezvous.manager');

        $rdv = $dm->getRepository('AppBundle:RendezVous')->findOneById($request->get('id'));
        $technicien = $dm->getRepository('AppBundle:Compte')->findOneById($request->get('technicien'));

        $rdv->setDateDebut(new \DateTime($request->get('start')));
        $rdv->setDateFin(new \DateTime($request->get('end')));

        $dm->flush();

        $response = new Response(json_encode($rdv->getEventJson($technicien->getCouleur())));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/calendar/populate", name="calendarPopulate", options={"expose" = "true"})
     */
    public function calendarPopulateAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $technicien = $dm->getRepository('AppBundle:Compte')->findOneById($request->get('technicien'));
        $periodeStart = $request->get('start');
        $periodeEnd = $request->get('end');
        $rdvs = $dm->getRepository('AppBundle:RendezVous')->findByDateAndParticipant($periodeStart, $periodeEnd, $technicien);

        $calendarData = array();

        foreach ($rdvs as $rdv) {
            $calendarData[] = $rdv->getEventJson($technicien->getCouleur());
        }

        $response = new Response(json_encode($calendarData));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/calendar/read", name="calendarRead")
     */
    public function calendarReadAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $rvm = $this->get('rendezvous.manager');
        $technicien = $request->get('technicien');
        if($request->get('passage') && !$request->get('id')) {
            $passage = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('passage'));
            $rdv = $rvm->createFromPassage($passage);
        } elseif($request->get('id')) {
            $rdv = $dm->getRepository('AppBundle:RendezVous')->findOneById($request->get('id'));
        }

        if(!$rdv) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf("Le rendez-vous \"%s\" n'a pas été trouvé", $request->get('id')));
        }

        $edition = (!$rdv->getPassage() || (!$rdv->getPassage()->isRealise()));

        if(!$edition) {

            return $this->render('calendar/rendezVous.html.twig', array('rdv' => $rdv));
        }

        $form = $this->createForm(new RendezVousType($dm), $rdv, array(
            'action' => $this->generateUrl('calendarRead', array('id' => ($rdv->getId()) ? $rdv->getId() : null, 'passage' => ($rdv->getPassage()) ? $rdv->getPassage()->getId() : null)),
            'method' => 'POST',
            'attr' => array('id' => 'eventForm')
        ));

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            return $this->render('calendar/rendezVous.html.twig', array('rdv' => $rdv, 'form' => $form->createView()));
        }

        if(!$rdv->getId()) {
            $dm->persist($rdv);
        }

        $dm->flush();

        return new Response(json_encode(array("success" => true)));
    }

    /**
     * @Route("/calendar/delete", name="calendarDelete", options={"expose" = "true"})
     */
    public function calendarDeleteAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $passageToDelete = $dm->getRepository('AppBundle:Passage')->findOneById($request->get('passage'));
        $etablissement = $passageToDelete->getEtablissement()->getId();
        $technicien = $request->get('technicien');

        if (!$passageToDelete->isRealise()) {
            $passageToDelete->setDateFin(null);
            $passageManager = new PassageManager($dm);

            $nextPassage = $passageManager->updateNextPassageEnAttente($passageToDelete);
            if ($passageToDelete->isAPlanifie()) {
                $passageToDelete->setDateDebut(null);
            }
            if ($nextPassage) {
                $dm->persist($nextPassage);
            }
            $dm->flush();
            $dm->persist($passageToDelete);
            $dm->flush();
        }
        if ($technicien) {
            return $this->redirect($this->generateUrl('calendar', array('passage' => $request->get('passage'), 'technicien' => $technicien)));
        } else {
            return $this->redirect($this->generateUrl('passage_etablissement', array('id' => $etablissement)));
        }
    }

}

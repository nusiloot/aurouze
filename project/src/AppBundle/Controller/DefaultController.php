<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Passage as Passages;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
       $passage = new \AppBundle\Document\Passages();
        $passage->setPrice(11);
        $passage->setName('test');

        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($passage);
        $dm->flush();

        return new Response('Created product id ' . $passage->getId());

        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }
    
    /**
     * @Route("/calendar", name="calendar")
     */
    public function calendarAction(Request $request) {
    	
    
    	return $this->render('default/calendar.html.twig');
    }

}

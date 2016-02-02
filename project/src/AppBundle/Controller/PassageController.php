<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PassageController extends Controller {
    
    /**
     * @Route("/passage", name="passage")
     */
    public function indexAction(Request $request) {
        $etablissement = $request->get('etablissement');
        
        return $this->render('passage/index.html.twig');
    }

}

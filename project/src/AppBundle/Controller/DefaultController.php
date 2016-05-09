<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Type\ContratType;

class DefaultController extends Controller {

    /**
     * @Route("/", name="accueil")
     */
    public function indexAction() {

        return $this->redirectToRoute('societe');
    }

}

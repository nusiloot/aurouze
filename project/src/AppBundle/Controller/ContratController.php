<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Document\Etablissement;
use AppBundle\Document\Contrat;
use AppBundle\Type\ContratType;

class ContratController extends Controller {

    /**
     * @Route("/contrat/{identifiantEtablissement}/creation", name="contrat_creation")
     */
    public function creationAction(Request $request, $identifiantEtablissement) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissement = $dm->getRepository('AppBundle:Etablissement')->findOneByIdentifiant($identifiantEtablissement);
        $contrat = $this->get('contrat.manager')->create($etablissement);
        
        $form = $this->createForm(new ContratType(), $contrat, array(
          'action' => $this->generateUrl('contrat_creation', array('identifiantEtablissement' => $etablissement->getIdentifiant())),
          'method' => 'POST',
          ));

        $dm->persist($contrat);
        $dm->flush();
        return $this->render('contrat/creationForm.html.twig', array('etablissement' => $etablissement, 'contrat' => $contrat, 'form' => $form->createView()));
    }

}

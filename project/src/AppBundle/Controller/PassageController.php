<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\EtablissementChoiceType;

class PassageController extends Controller {
    
    /**
     * @Route("/passage", name="passage")
     */
    public function indexAction(Request $request) {
        $formEtablissement = $this->createForm(new EtablissementChoiceType(), null, array(
            'action' => $this->generateUrl('passage_etablissement_choice'),
            'method' => 'POST',
        ));

        $passages = $this->get('passage.manager')->getRepository()->findToPlan();
        
        return $this->render('passage/index.html.twig', array('passages' => $passages, 'formEtablissement' => $formEtablissement->createView()));
    }

    /**
     * @Route("/passage/etablissement-choix", name="passage_etablissement_choice")
     */
    public function etablissementChoiceAction(Request $request) {
        $formData = $request->get('etablissement_choice');

        return $this->redirectToRoute('passage_etablissement', array('identifiantEtablissement' => $formData['etablissements']));
    }

    /**
     * @Route("/passage/{identifiantEtablissement}", name="passage_etablissement")
     */
    public function etablissementAction(Request $request, $identifiantEtablissement) {
        $etablissement = $this->get('etablissement.manager')->getRepository()->findOneByIdentifiant($identifiantEtablissement);
        $passages = $this->get('passage.manager')->getRepository()->findPassagesForEtablissement($etablissement->getIdentifiant());

        return $this->render('passage/etablissement.html.twig', array('etablissement' => $etablissement, 'passages' => $passages));
    }
}

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
        $geojson = new \stdClass();
        $geojson->type = "FeatureCollection";
        $geojson->features = array();
        foreach($passages as $passage) {
            $feature = new \stdClass();
            $feature->type="Feature";
            $feature->properties = new \stdClass();
            $feature->geometry = new \stdClass();
            $feature->geometry->type = "Point";
            $feature->geometry->coordinates = array($passage->getPassageEtablissement()->getCoordinates()->getX(), $passage->getPassageEtablissement()->getCoordinates()->getY());
            $geojson->features[] =  $feature;
        }

        return $this->render('passage/index.html.twig', array('passages' => $passages, 'formEtablissement' => $formEtablissement->createView(), 'geojson' => $geojson));
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

        $formEtablissement = $this->createForm(new EtablissementChoiceType(), array('etablissements' => $etablissement->getIdentifiant(), 'etablissement' => $etablissement), array(
            'action' => $this->generateUrl('passage_etablissement_choice'),
            'method' => 'POST',
        ));

        return $this->render('passage/etablissement.html.twig', array('etablissement' => $etablissement, 'passages' => $passages, 'formEtablissement' => $formEtablissement->createView()));
    }
}

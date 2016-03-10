<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\EtablissementChoiceType;
use AppBundle\Document\Etablissement;

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

        $geojson = $this->buildGeoJson($passages);

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

        $geojson = $this->buildGeoJson(array($etablissement));
        $formEtablissement = $this->createForm(new EtablissementChoiceType(), array('etablissements' => $etablissement->getIdentifiant(), 'etablissement' => $etablissement), array(
            'action' => $this->generateUrl('passage_etablissement_choice'),
            'method' => 'POST',
        ));

        return $this->render('passage/etablissement.html.twig', array('etablissement' => $etablissement, 'passages' => $passages, 'formEtablissement' => $formEtablissement->createView(), 'geojson' => $geojson));
    }

    private function buildGeoJson($listDocuments) {
        $geojson = new \stdClass();
        $geojson->type = "FeatureCollection";
        $geojson->features = array();
        foreach ($listDocuments as $document) {
            $feature = new \stdClass();
            $feature->type = "Feature";
            $feature->properties = new \stdClass();
            $feature->properties->_id = $document->getId();
            $etbInfos = $document;
            $coordinates = null;
            if (!($document instanceof Etablissement)) {
                $etbInfos = $document->getPassageEtablissement();
                $coordinates = $document->getPassageEtablissement()->getCoordinates();
            } else {
                $coordinates = $document->getAdresse()->getCoordinates();
            }
            $feature->properties->nom = $etbInfos->getNom();
            $feature->properties->color = 'orange';
            $feature->properties->icon = 'mdi-' . $etbInfos->getIconTypeEtb();
            $feature->geometry = new \stdClass();
            $feature->geometry->type = "Point";
            $feature->geometry->coordinates = array($coordinates->getY(),$coordinates->getX());
            $geojson->features[] = $feature;
        }
        return $geojson;
    }

}

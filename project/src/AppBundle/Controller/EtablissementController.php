<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Manager\PassageManager as PassageManager;
use AppBundle\Manager\EtablissementManager as EtablissementManager;
use AppBundle\Type\EtablissementChoiceType as EtablissementChoiceType;

class DefaultController extends Controller {

    /**
     * @Route("/etablissement-choix", name="etablissement_choice")
     */
    public function choiceAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $form = $this->createForm(new EtablissementChoiceType(), array(
            'action' => $this->generateUrl('etablissement_choice'),
            'method' => 'POST',
        ));

        return $this->render('default/etablissementChoixForm.html.twig', array('etablissements' => $etablissements));
    }

    /**
     * @Route("/etablissement-search", name="etablissement_search")
     */
    public function searchAction(Request $request) {

        $term = $request->get('term');
        $response = new Response();
        $etablissementsResult = array();
        if (strlen($term) > 3) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $etablissementsByNom = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'nom');
            $etablissementsByCommune = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'commune');
            $this->contructSearchResult($etablissementsByNom,$etablissementsResult);
             $this->contructSearchResult($etablissementsByCommune,$etablissementsResult);
        }
        $data = json_encode($etablissementsResult);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($data);
        return $response;
    }

    public function contructSearchResult($etablissementsByCriteria, &$etablissementsResult) {

        foreach ($etablissementsByCriteria as $etablissement) {
            $newResult = new \stdClass();
            $newResult->id = $etablissement->getIdentifiant();
            $newResult->term = $etablissement->getIntitule();
            $etablissementsResult[] = $newResult;
        }
    }

}

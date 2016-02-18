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
     * @Route("/", name="accueil")
     */
    public function indexAction(Request $request) {

        return $this->redirectToRoute('passage');

        /* if($request->get("etablissement_choice") && count($request->get("etablissement_choice"))){
          $etb_choices = $request->get("etablissement_choice");
          if($etb_choices["etablissements"]){
          return $this->redirectToRoute('passageEtablissement',array('identifiantEtablissement' => $etb_choices["etablissements"]));
          }
          }
          $dm = $this->get('doctrine_mongodb')->getManager();
          $form = $this->createForm(new EtablissementChoiceType(), array(
          'action' => $this->generateUrl('etablissement_choice'),
          'method' => 'POST',
          ));

          return $this->render('default/etablissementChoixForm.html.twig', array('form' => $form->createView())); */
    }

    /**
     * @Route("/etablissement-search", name="etablissement_search")
     */
    public function etablissementSearchAction(Request $request) {

        $term = $request->get('term');
        $response = new Response();
        $etablissementsResult = array();
        if (strlen($term) > 3) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $etablissementsByNom = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'nom');
            $etablissementsByAdresse = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'adresse.adresse');
            $etablissementsByCp = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'adresse.code_postal');
            $etablissementsByCommune = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term, 'adresse.commune');
            $this->contructSearchResult($etablissementsByNom, $etablissementsResult);
            $this->contructSearchResult($etablissementsByAdresse, $etablissementsResult);
           $this->contructSearchResult($etablissementsByCp, $etablissementsResult);
            $this->contructSearchResult($etablissementsByCommune, $etablissementsResult);
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
            $newResult->term = $etablissement->getNom() . ' ' . $etablissement->getAdresse()->getAdresse()
                        . ' ' . $etablissement->getAdresse()->getCodePostal()
                        . ' ' . $etablissement->getAdresse()->getCommune();
            $etablissementsResult[] = $newResult;
        }
    }

}

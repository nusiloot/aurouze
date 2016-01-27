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

        $dm = $this->get('doctrine_mongodb')->getManager();
        $form = $this->createForm(new EtablissementChoiceType(), array(
            'action' => $this->generateUrl('etablissement_choice'),
            'method' => 'POST',
        ));

        return $this->render('default/etablissementChoixForm.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/etablissement-choix", name="etablissement_choice")
     */
    public function etablissementChoiceAction(Request $request) {

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
    public function etablissementSearchAction(Request $request) {

        $term = $request->get('term');
        $response = new Response();
        $etablissementsResult = array();
        if (strlen($term) > 3) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $etablissements = $dm->getRepository('AppBundle:Etablissement')->findByTerm($term);
            foreach ($etablissements as $etablissement) {
                $newResult = new \stdClass();
                $newResult->id = $etablissement->getIdentifiant();
                $newResult->term = $etablissement->getNom() . ' ' . $etablissement->getAdresse()->getAdresse()
                        . ' ' . $etablissement->getAdresse()->getCodePostal()
                        . ' ' . $etablissement->getAdresse()->getCommune();
                $etablissementsResult[] = $newResult;
            }
        }
        $data = json_encode($etablissementsResult);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($data);
        return $response;
    }

    /**
     * @Route("/passage/{identifiantEtablissement}", name="passageEtablissement")
     */
    public function passageEtablissementAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissementManager = new EtablissementManager($dm);
        $etablissement = $etablissementManager->create();
        $dm->persist($etablissement);
        $dm->flush();

        return new Response('Created product id ' . $etablissement->getId());

        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }

    /**
     * @Route("/creation-passage/{identifiantEtablissement}", name="passageCreate")
     */
    public function passageCreateAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissement = $dm->getRepository('AppBundle:Etablissement')->findOneByIdentifiant($request->get('identifiantEtablissement'));

        $passageManager = new PassageManager($dm);
        $passage = $passageManager->create($etablissement);
        $dm->persist($passage);
        $dm->flush();

        return new Response('Created passage id ' . $passage->getId());

        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }

    /**
     * @Route("/creation-etablissement", name="etablissementCreate")
     */
    public function etablissementCreateAction(Request $request) {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $etablissementManager = new EtablissementManager($dm);
        $etablissement = $etablissementManager->create();
        $dm->persist($etablissement);
        $dm->flush();

        return new Response('Created product id ' . $etablissement->getId());

        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }

}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\TechnicienChoiceType as TechnicienChoiceType;
use AppBundle\Document\Compte;
use AppBundle\Manager\ContratManager;
use AppBundle\Manager\PassageManager;
use AppBundle\Type\CompteType;

class CompteController extends Controller {

    /**
     * @Route("/comptes", name="comptes")
     */
    public function comptesAction(Request $request) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $comptes = $dm->getRepository('AppBundle:Compte')->findAllUtilisateurs();
        $contratManager = new ContratManager($dm);
        $passageManager = new PassageManager($dm);
        return $this->render('compte/listing.html.twig', array('comptes' => $comptes, 'contratManager' => $contratManager, 'passageManager' => $passageManager));
    }

    /**
     * @Route("/compte/{societe}/modification/{id}", defaults={"id" = null}, name="compte_modification")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function modificationAction(Request $request, $societe, $id) {

        $dm = $this->get('doctrine_mongodb')->getManager();

        $compte = null;
        if ($id) {
            $compte = $this->get('compte.manager')->getRepository()->find($id);
        } else {

            $compte = new Compte($societe);
            $compte->setSociete($societe);
            $dm->persist($compte);
        }


        $form = $this->createForm(new CompteType($this->container, $dm), $compte, array(
            'action' => $this->generateUrl('compte_modification', array('societe' => $societe->getId(), 'id' => $id)),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $compte = $form->getData();
            $dm->persist($compte);
            $dm->flush();
            return $this->redirectToRoute('societe_visualisation', array('id' => $societe->getId()));
        }

        return $this->render('compte/modification.html.twig', array('societe' => $societe, 'form' => $form->createView(), 'compte' => $compte));
    }

    /**
     * @Route("/compte/{id}/etat", name="compte_update_etat")
     * @ParamConverter("compte", class="AppBundle:Compte")
     */
    public function updateEtatAction(Request $request, Compte $compte) {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        if ($compte) {
            try {
                $compte->setActif($request->get('etat'));
                $dm->persist($compte);
                $dm->flush();
                return new Response(json_encode(array("success" => true)));
            } catch (\Exception $e) {
                
            }
        }

        throw new \Exception('Une erreur s\'est produite');
    }

}

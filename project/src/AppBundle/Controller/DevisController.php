<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Manager\DevisManager;
use AppBundle\Document\Devis;
use AppBundle\Document\Societe;
use AppBundle\Type\DevisType;

/**
 * Devis controller.
 *
 * @Route("/devis")
 */
class DevisController extends Controller {


    /**
     * @Route("/societe/{societe}/creation-devis", name="devis_creation")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function creationAction(Request $request, Societe $societe) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $cm = $this->get('configuration.manager');
        $devism = $this->get('devis.manager');

        if ($request->get('id')) {
            $devis = $devism->getRepository()->findOneById($request->get('id'));
        }

        if ($request->get('id') && !$devis) {

            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf("Le devis %s n'a pas été trouvé", $request->get('id')));
        }


        if (!isset($devis)) {
            $devis = $devism->createVierge($societe);
        }

        $devis->setSociete($societe);

        if(!$devis->getId()) {
            $devis->setDateEmission(new \DateTime());
        }

        $appConf = $this->container->getParameter('application');
        if(!$devis->getCommercial()) {
            $commercial = $dm->getRepository('AppBundle:Compte')->findOneByIdentifiant($appConf['commercial']);
            if ($commercial === null) {
                throw new \LogicException("Il n'y a pas de commercial dans la config.");
            }
            $devis->setCommercial($commercial);
        }


        $produitsSuggestion = array();
        foreach ($cm->getConfiguration()->getProduits() as $produit) {
            $produitsSuggestion[] = array("libelle" => $produit->getNom(), "conditionnement" => $produit->getConditionnement(), "identifiant" => $produit->getIdentifiant(), "prix" => $produit->getPrixVente());
        }

        $form = $this->createForm(new DevisType($dm, $cm, $appConf['commercial']), $devis, array(
            'action' => "",
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            return $this->render('devis/libre.html.twig', array('form' => $form->createView(), 'produitsSuggestion' => $produitsSuggestion, 'societe' => $societe, 'devis' => $devis));
        }

        $devis->update();

        if ($request->get('previsualiser')) {

            return $this->pdfAction($request, $devis);
        }

        $dm->flush();

        return $this->redirectToRoute('facture_societe', array('id' => $societe->getId()));
    }

    /**
     * @Route("/societe/{societe}/devis/{id}/suppression", name="devis_suppression")
     * @ParamConverter("societe", class="AppBundle:Societe")
     * @ParamConverter("devis", class="AppBundle:Devis")
     */
    public function suppressionAction(Request $request, Societe $societe, Devis $devis) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        return $this->redirectToRoute('devis_societe', array('id' => $societe->getId()));
    }

}

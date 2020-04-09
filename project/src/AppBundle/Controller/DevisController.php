<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Manager\DevisManager;
use AppBundle\Document\Devis;
use AppBundle\Document\Societe;
use AppBundle\Document\RendezVous;
use AppBundle\Type\DevisType;

/**
 * Devis controller.
 *
 * @Route("/devis")
 */
class DevisController extends Controller
{
    /**
     * @Route("/", name="devis")
     */
    public function indexAction()
    {
        $devisManager = $this->get('devis.manager');

        $devisEnAttente = $devisManager->getRepository('AppBundle:Devis')->findBy(
            ['dateSignature' => null], ['dateEmission' => 'desc']
        );

        return $this->render('devis/index.html.twig', compact('devisEnAttente'));
    }

    /**
     * @Route("/societe/{id}", name="devis_societe")
     *
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function societeAction(Societe $societe)
    {
        $devisManager = $this->get('devis.manager');

        $devis = $devisManager->findBySociete($societe);

        return $this->render('devis/societe.html.twig',
            compact('societe', 'devis')
        );
    }

    /**
     * @Route("/societe/{societe}/creation-devis", name="devis_creation")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function creationAction(Request $request, Societe $societe) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $cm = $this->get('configuration.manager');
        $devism = $this->get('devis.manager');

        $devis = $devism->createVierge($societe);
        $devis->setSociete($societe);
        $devis->setDateEmission(new \DateTime());

        $appConf = $this->container->getParameter('application');
        $commercial = $dm->getRepository('AppBundle:Compte')->findOneByIdentifiant($appConf['commercial']);
        if ($commercial === null) {
            throw new \LogicException("Il n'y a pas de commercial dans la config.");
        }
        $devis->setCommercial($commercial);

        $produitsSuggestion = $this->getProduitsSuggestion($cm->getConfiguration()->getProduits());

        $form = $this->createForm(new DevisType($dm, $cm, $appConf['commercial'], $societe),
                                  $devis,
                                  array(
                                    'action' => "",
                                    'method' => 'POST',
                                  )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rdv = new RendezVous();
            $rdv->setDateDebut($devis->getDateEmission());
            $devis->setRendezvous($rdv);

            $devis->update();

            $dm->persist($rdv);
            $dm->persist($devis);
            $dm->flush();

            return $this->redirectToRoute('calendar', array('devis' => $devis->getId(), 'id' => $devis->getSociete()->getId(), 'technicien' => $devis->getCommercial()->getId()));
            //return $this->redirectToRoute('devis_societe', array('id' => $societe->getId()));
        }

        return $this->render('devis/modification.html.twig', array('form' => $form->createView(), 'produitsSuggestion' => $produitsSuggestion, 'societe' => $societe, 'devis' => $devis));
    }

    /**
     * @Route("/societe/{societe}/devis/{id}/suppression", name="devis_suppression")
     * @ParamConverter("societe", class="AppBundle:Societe")
     * @ParamConverter("devis", class="AppBundle:Devis")
     */
    public function suppressionAction(Request $request, Societe $societe, Devis $devis) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->remove($devis);
        $dm->flush();

        return $this->redirectToRoute('devis_societe', array('id' => $societe->getId()));
    }

    /**
     * @Route("/{id}/edit", name="devis_modification")
     */
    public function editAction(Request $request, Devis $devis)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $cm = $this->get('configuration.manager');
        $appConf = $this->container->getParameter('application');

        $produitsSuggestion = $this->getProduitsSuggestion($cm->getConfiguration()->getProduits());

        $form = $this->createForm(new DevisType($dm, $cm, $appConf['commercial']), $devis, array(
            'action' => "",
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devis->update();
            $dm->persist($devis);
            $dm->flush();

            return $this->redirectToRoute('devis_societe', ['id' => $devis->getSociete()->getId()]);
        }

        return $this->render('devis/modification.html.twig', [
            'devis' => $devis,
            'form' => $form->createView(),
            'produitsSuggestion' => $produitsSuggestion
        ]);
    }

    /**
     * @Route("/{id}/pdf", name="devis_pdf")
     */
    public function pdfAction(Devis $devis)
    {

    }

    private function getProduitsSuggestion($produits)
    {
        $produitsSuggestion = [];

        foreach ($produits as $produit) {
            $produitsSuggestion[] = array("libelle" => $produit->getNom(), "conditionnement" => $produit->getConditionnement(), "identifiant" => $produit->getIdentifiant(), "prix" => $produit->getPrixVente());
        }

        return $produitsSuggestion;
    }
}

<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Document\Passage;

class AjaxController extends Controller
{
    /**
     * @Route("/ajax/passage/{secteur}/visualisation/{mois}", name="ajax_passage", defaults={"secteur"="PARIS"})
     */

    public function listPassageAction($secteur, $mois = null)
    {
    }

    /**
     * @Route("/ajax/passage/{passage}/infos", name="ajax_more_infos_passage")
     */
    public function showInformationsAction(Passage $passage)
    {
        $infossup = [];

        $etablissement = $passage->getEtablissement();
        $infossup['etablissement']['nom'] = $etablissement->getNom();
        $infossup['etablissement']['commentaire'] = $etablissement->getCommentaire();
        $infossup['etablissement']['contact']['telephone'] = $etablissement->getTelephoneFixe();
        $infossup['etablissement']['contact']['portable'] = $etablissement->getTelephonePortable();
        $infossup['etablissement']['contact']['fax'] = $etablissement->getFax();
        $infossup['etablissement']['contact']['libelle'] = $etablissement->getContactCoordonnee()->getLibelle();
        $infossup['etablissement']['contact']['email'] = $etablissement->getEmail();

        $contrat = $passage->getContrat();
        $infossup['contrat']['nomenclature'] = $contrat->getNomenclature();

        $societe = $contrat->getSociete();
        $infossup['societe']['id'] = $societe->getIdentifiant();
        $infossup['societe']['nom'] = $societe->getRaisonSociale();
        $infossup['societe']['icon'] = $societe->getIcon();

        $facture = $this->get('facture.manager');
        $infossup['facture']['retard'] = $facture->getRetardDePaiementBySociete($societe);

        return $this->render('passage/infossupplementaires.html.twig', compact('infossup'));
    }
}

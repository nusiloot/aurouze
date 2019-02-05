<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Document\Passage;

class AjaxController extends Controller
{
    /**
     * @Route("/ajax/passage/{passage}/infos", name="ajax_more_infos_passage")
     */
    public function showInformationsAction(Passage $passage)
    {
        $etablissement = $passage->getEtablissement();
        $contrat = $passage->getContrat();
        $societe = $contrat->getSociete();
        $facture = $this->get('facture.manager');

        return $this->render('passage/infossupplementaires.html.twig',
            [
                'passage' => $passage,
                'etablissement' => $etablissement,
                'contrat' => $contrat,
                'societe' => $societe,
                'retard' => count($facture->getRetardDePaiementBySociete($societe)) > 0
            ]
        );
    }
}

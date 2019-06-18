<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImportEtablissement
 *
 * @author mathurin
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\PassageManager;
use Symfony\Component\Console\Helper\ProgressBar;
use AppBundle\Manager\ContratManager;
use AppBundle\Manager\FactureManager;

class FactureUpdateEmetteurCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:facture-update-emetteur')
                ->setDescription('Facture update emetteur');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        echo "\nMis à jour des factures avec leur emetteur...\n";
        $this->updateFacturesEmetteur($output);
    }

    public function updateFacturesEmetteur($output) {

        $allFactures = $this->dm->getRepository('AppBundle:Facture')->findAll();

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
            

        $fm = $this->getContainer()->get('facture.manager');
        $parameters = $fm->getParameters();

        foreach ($allFactures as $facture) {
            
            $facture->getEmetteur()->setNom($parameters['emetteur']['nom']);
            $facture->getEmetteur()->setRaisonSociale($parameters['emetteur']['nom']);
            $facture->getEmetteur()->setAdresse($parameters['emetteur']['adresse']);
            $facture->getEmetteur()->setCodePostal($parameters['emetteur']['code_postal']);
            $facture->getEmetteur()->setCommune($parameters['emetteur']['commune']);
            $facture->getEmetteur()->setTelephone($parameters['emetteur']['telephone']);
            $facture->getEmetteur()->setFax($parameters['emetteur']['fax']);
            $facture->getEmetteur()->setEmail($parameters['emetteur']['email']);

            $cptTotal++;
            if ($cptTotal % (count($allFactures) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 1000) {
                 $this->dm->flush();
                $i = 0;
            }
            $i++;
        }
         $this->dm->flush();
        $progress->finish();
    }

}

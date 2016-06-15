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

class FactureCorrectionTVACommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('facture:correction-tva')
                ->setDescription('Facture correction de TVA')
                ->addArgument(
                    'numero', InputArgument::REQUIRED, 'Numéro de la facture'
                )
                ->addArgument(
                    'tauxTva', InputArgument::REQUIRED, 'Taux de TVA à appliquer'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $fm = $this->getContainer()->get('facture.manager');
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $facture = $fm->getRepository()->findOneBy(array('numeroFacture' => $input->getArgument('numero')));

        if(!$facture) {
            // $output->writeln(sprintf("<error>La facture %s n'a pas été trouvé</error>", $input->getArgument('numero')));
            return;
        }

        $taux = (float) $input->getArgument('tauxTva');
        $tauxOriginal = $facture->getTva();
        if($taux == $facture->getTva()) {
            return;
        }

        foreach($facture->getLignes() as $ligne) {
            $ligne->setTauxTaxe($taux);
            if($ligne->isOrigineContrat()) {
                $ligne->getMouvement()->setTauxTaxe($taux);
            }
        }

        $facture->updateCalcul();
        $facture->updateRestantAPayer();

        $dm->flush();

        $output->writeln("<info>".$facture->getId()."</info> passé de ".$tauxOriginal." à ".$taux);
    }

}

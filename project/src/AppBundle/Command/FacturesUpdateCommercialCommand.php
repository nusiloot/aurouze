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

class FacturesUpdateCommercialCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('facture:update-commerciaux')
                ->setDescription('Facture mise à jour des commerciaux')
                ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $fm = $this->getContainer()->get('facture.manager');
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $factures = $fm->getRepository()->findAll();
        
        $appConf = $this->getContainer()->getParameter('application');
        
        $defaultCom = $dm->getRepository('AppBundle:Compte')->findOneByIdentifiant($appConf['commercial']);

        if(!$factures) {
            return;
        }
        $cpt = 0;
        foreach ($factures as $facture) {
          if(!$facture->getContrat()){
            if($facture->getCommercial()){
              $output->writeln("<info>".$facture->getId()."</info> à déjà un commerical : ".$facture->getCommercial()->getIdentite());
            }else{
              $output->writeln("<info>".$facture->getId()."</info> va être assigné à : ".$defaultCom->getIdentite());
               $facture->setCommercial($defaultCom);
            }
          }

          $cpt++;
          if($cpt%1000 == 0){
            $dm->flush();
            $cpt = 0;
          }
        }
        $dm->flush();
    }

}

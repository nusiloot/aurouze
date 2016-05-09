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

class ShowPassageMultiTechCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('passage:show-multi-technicien')
                ->setDescription('Passages multi technicien');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $qb = $dm->createQueryBuilder('AppBundle:Passage')->field('techniciens.1')->exists(true);
        $query = $qb->getQuery();
        $results = $query->execute();
        
        foreach ($results as $result) {
        	$output->writeln(sprintf("\n<comment>Passage : %s du contrat %s a %s techniciens !</comment>", $result->getId(), $result->getContrat()->getId(), count($result->getTechniciens())));
        }
       
    }

   

}

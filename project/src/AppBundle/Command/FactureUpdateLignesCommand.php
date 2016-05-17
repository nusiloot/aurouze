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
use Doctrine\Common\Collections\ArrayCollection;

class FactureUpdateLignesCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:facture-update-lignes')
                ->setDescription('Facture update lignes');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        
        $allFactures = $this->dm->getRepository('AppBundle:Facture')->findAll();
        
        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        $nb = count($allFactures);
        foreach ($allFactures as $facture) {
        	if (count($facture->getLignes()) > 0) {
	        	foreach ($facture->getLignes() as $factureLigne) {
	        		try {
	        			$mvt = $factureLigne->getMouvement();
	        		} catch (\Exception $e) {
	        			continue;
	        		}
	        		if ($mvt) {
	        			$factureLigne->pullFromMouvement($mvt);
	        		}
	        	}
	        	$facture->update();
	        	$cptTotal++;
	        	if ($cptTotal % ($nb / 100) == 0) {
	        		$progress->advance();
	        	}
	        	if ($i >= 1000) {
	        		$this->dm->flush();
	        		$i = 0;
	        	}
	        	$i++;
        	}
        }
        $this->dm->flush();
        $progress->finish();
    }

}

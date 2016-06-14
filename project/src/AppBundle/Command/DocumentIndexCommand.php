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

class DocumentIndexCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('index:documents')
                ->setDescription('Index documents');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $this->dm->getSchemaManager()->ensureIndexes();
    }

}

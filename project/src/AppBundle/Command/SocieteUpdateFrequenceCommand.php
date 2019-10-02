<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\PassageManager;
use Symfony\Component\Console\Helper\ProgressBar;
use AppBundle\Manager\ContratManager;
use Symfony\Component\Debug\Exception\UndefinedMethodException;

class SocieteUpdateFrequenceCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:societe-update-frequence')
                ->setDescription('Mettre à jour les fréquences des sociétés')
                ->addArgument('pathfilename', InputArgument::REQUIRED, "Path of file csv for the old societies");

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $filename = $input->getArgument("pathfilename");

        $filename = getcwd()."/src/AppBundle/Command/".$filename;


        if(is_file($filename)){

            $listOldSocieties = $this->getCsvToArray($filename);

            foreach ($listOldSocieties as $key => $oldSociety) {

                list($society) = $this->dm->getRepository('AppBundle:Societe')->findByIdentifiantReprise($oldSociety["id"]);

                if(!$society->getFrequencePaiement()){

                    echo " Society id: ".$society->getId()." Old = null  affected = ".$oldSociety["frequence"]."\n";

                    $society->setFrequencePaiement($oldSociety["frequence"]);

                    $this->dm->persist($society);
                    
                    $this->dm->flush();
                }
            }
        $listSocietiesEmptyFreq = $this->dm->getRepository('AppBundle:Societe')->findAllFrequencePaiement(null);

        $frequence = "RECEPTION";

        foreach ($listSocietiesEmptyFreq as $key => $society) {

            echo " Society id: ".$society->getId()." Old = null  affected = ".$frequence."\n";

            $society->setFrequencePaiement($frequence);

            $this->dm->persist($society);
            
            $this->dm->flush();
        } 
        }
        
        


    }

    public function getCsvToArray(string $filename){
        $dataArray = [];
        $row = 1;
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $num = count($data);
                //echo "<p> $num champs à la ligne $row: <br /></p>\n";
                $row++;
                for ($c=1; $c < $num; $c++) {
                    //echo $data[$c] . "<br />\n";
                    if($c ==1){
                        $dataArray[$row]["id"] = $data[$c];
                    }
                    if($c ==2){
                        $dataArray[$row]["frequence"] = $data[$c];

                    }
                }
            }
            fclose($handle);
        }
        return $dataArray;
    }

}

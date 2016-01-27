<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Import;

/**
 * Description of EtablissementCsvImport
 *
 * @author mathurin
 */
use AppBundle\Document\Passage;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\PassageManager;
use AppBundle\Manager\EtablissementManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Import\CsvFile;

class PassageCsvImporter {

    protected $dm;
    protected $pm;
    protected $em;

    const CSV_DATE_CREATION = 0;
    const CSV_ETABLISSEMENT_ID = 1;
    const CSV_DATE_DEBUT = 2;
    const CSV_DATE_FIN = 3;
    const CSV_DUREE = 4;
    const CSV_TECHNICIEN = 5;
    const CSV_LIBELLE = 6;
    const CSV_DESCRIPTION = 7;

    public function __construct(DocumentManager $dm, PassageManager $pm, EtablissementManager $em) {
        $this->dm = $dm;
        $this->pm = $pm;
        $this->em = $em;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();

        $i=0;

        foreach ($csv as $data) {

            $etablissement = $this->em->getRepository()->findOneByIdentifiant($data[self::CSV_ETABLISSEMENT_ID]);
            
            if (!$etablissement) {
                //$output->writeln(sprintf("<error>L'établissement %s n'existe pas</error>", $data[self::CSV_ETABLISSEMENT_ID]));
                continue;
            }

            $passage = new Passage();
            $passage->setEtablissementIdentifiant($etablissement->getIdentifiant());
            $passage->setDateCreation(new \DateTime($data[self::CSV_DATE_CREATION]));
            $passage->updateEtablissementInfos($etablissement);
            $passage->setNumeroPassageIdentifiant($this->pm->getNextNumeroPassage($passage->getEtablissementIdentifiant(), $passage->getDateCreation()));       
            $passage->setId($passage->generateId());
            $passage->setDateDebut(new \DateTime($data[self::CSV_DATE_DEBUT]));
            $passage->setDateFin($passage->getDateDebut()->modify(sprintf("+%s minutes", $data[self::CSV_DUREE])));
            $passage->setLibelle($data[self::CSV_LIBELLE]);
            $passage->setDescription($data[self::CSV_DESCRIPTION]);
            $passage->setTechnicien(trim($data[self::CSV_TECHNICIEN]));
            $this->dm->persist($passage);
            $output->writeln(sprintf("<info>Création du passage %s</info>", $passage->getId()));
            $i++;

            if($i >= 1000) {
                $this->dm->flush();
                $i=0;
            }

        }

        $this->dm->flush();
    }

}

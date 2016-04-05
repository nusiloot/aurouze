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
use AppBundle\Document\Contrat;
use AppBundle\Document\ContratPassages;
use AppBundle\Document\Prestation;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\PassageManager;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Manager\UserManager;
use AppBundle\Manager\ContratManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Import\CsvFile;
use Behat\Transliterator\Transliterator;
use Symfony\Component\Console\Helper\ProgressBar;

class PassageCsvImporter {

    protected $dm;
    protected $pm;
    protected $em;
    protected $um;
    protected $cm;

    const CSV_DATE_CREATION = 0;
    const CSV_ETABLISSEMENT_ID = 1;
    const CSV_DATE_DEBUT = 2;
    const CSV_DATE_FIN = 3;
    const CSV_DUREE = 4;
    const CSV_TECHNICIEN = 5;
    const CSV_LIBELLE = 6;
    const CSV_DESCRIPTION = 7;
    const CSV_CONTRAT_ID = 8;
    const CSV_PRESTATIONS = 9;

    public function __construct(DocumentManager $dm, PassageManager $pm, EtablissementManager $em, UserManager $um, ContratManager $cm) {
        $this->dm = $dm;
        $this->pm = $pm;
        $this->em = $em;
        $this->um = $um;
        $this->cm = $cm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();

        $i = 0;
        $cptTotal = 0;

        $progress = new ProgressBar($output, 100);
        $progress->start();

        $prestationsType = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getPrestationsArray();

        foreach ($csv as $data) {
            if ($data[self::CSV_ETABLISSEMENT_ID] == "000000") {
                continue;
            }

            $etablissement = $this->em->getRepository()->findOneByIdentifiantReprise($data[self::CSV_ETABLISSEMENT_ID]);

            if (!$etablissement) {
                $output->writeln(sprintf("<error>L'établissement %s n'existe pas</error>", $data[self::CSV_ETABLISSEMENT_ID]));
                continue;
            }
            $passage = new Passage();
            $passage->setEtablissement($etablissement);
            $passage->setDatePrevision(new \DateTime($data[self::CSV_DATE_CREATION]));
            $passage->setNumeroPassageIdentifiant("001");
            $passage->generateId();

            if ($data[self::CSV_DATE_DEBUT]) {
                $passage->setDateDebut(new \DateTime($data[self::CSV_DATE_DEBUT]));
            } else {
                $passage->setDateDebut(clone $passage->getDatePrevision());
            }

            if (!$data[self::CSV_DUREE]) {
                $output->writeln(sprintf("<error>La durée du passage n'a pas été renseigné : %s</error>", $passage->getId()));
                continue;
            }
            if ($data[self::CSV_DATE_DEBUT]) {
                $passage->setDateFin(clone $passage->getDateDebut());
                $passage->getDateFin()->modify(sprintf("+%s minutes", $data[self::CSV_DUREE]));
            }
            $passage->setLibelle($data[self::CSV_LIBELLE]);
            $passage->setDescription(str_replace('\n', "\n", $data[self::CSV_DESCRIPTION]));

            $prestations = array();
            if ($data[self::CSV_PRESTATIONS]) {
                $prestations = explode(',', $data[self::CSV_PRESTATIONS]);
            }
            
            $passage->setPrestations($prestations);

            $prenomNomTechnicien = trim($data[self::CSV_TECHNICIEN]);

            $nomTechnicien = substr(strrchr($prenomNomTechnicien, " "), 1);
            $prenomTechnicien = trim(str_replace($nomTechnicien, '', $prenomNomTechnicien));
            $identifiantTechnicien = strtoupper(Transliterator::urlize($prenomTechnicien . ' ' . $nomTechnicien));

            $user = $this->um->getRepository()->findOneByIdentifiant($identifiantTechnicien);
            //LISTE DE TECHNICIEN A PREVOIR
            $passage->addTechnicien($user);

            $contrat = $this->cm->getRepository()->findOneByIdentifiantReprise($data[self::CSV_CONTRAT_ID]);
            if(!$contrat){
                 $output->writeln(sprintf("<error>Le contrat %s n'existe pas</error>", $data[self::CSV_CONTRAT_ID]));
                continue;
            }
            $passage->setContrat($contrat);
            $passage->setNumeroContratArchive($contrat->getNumeroArchive());
            $contrat->addPassage($etablissement,$passage);

            $this->dm->persist($contrat);
            $this->dm->persist($passage);

            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 1000) {
                $this->dm->flush();
                $i = 0;
            }
        }

        $this->dm->flush();
        $progress->finish();
    }

}

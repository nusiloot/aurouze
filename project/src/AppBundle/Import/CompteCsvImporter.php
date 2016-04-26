<?php

namespace AppBundle\Import;

use AppBundle\Document\Compte as Compte;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;
use Behat\Transliterator\Transliterator;

class CompteCsvImporter extends CsvFile {

    protected $dm;

    const CSV_IDENTIFIANT = 0;
    const CSV_IDENTITE = 1;
    const CSV_TYPE = 21;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();
        $cpt = 0;
        foreach ($csv as $data) {
            $compte = $this->createFromImport($data);
            if ($compte) {
                $this->dm->persist($compte);
            }
            if ($cpt > 1000) {
                $this->dm->flush();
                $cpt = 0;
            }
            $cpt++;
        }

        
        $this->dm->flush();
    }

    public function createFromImport($ligne) {
        $prenomNom = trim($ligne[self::CSV_IDENTITE]);
        $nom = substr(strrchr($prenomNom, " "), 1);
        $prenom = trim(str_replace($nom, '', $prenomNom));

        $identifiant = strtoupper(Transliterator::urlize($prenom . ' ' . $nom));
        $compte = $this->dm->getRepository('AppBundle:Compte')->findByIdentifiant($identifiant);
        if (isset($ligne[self::CSV_TYPE])) {
            if (!$compte) {
                $compte = new Compte();
                $compte->setIdentifiant($identifiant);
                $compte->generateId();
                $compte->setNom($nom);
                $compte->setPrenom($prenom);
                $compte->setCouleur($this->random_color());
                $compte->setType($ligne[self::CSV_TYPE]);
                return $compte;
            }
        }
        return false;
    }

    public function random_color_part() {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    public function random_color() {
        return '#' . $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }

}

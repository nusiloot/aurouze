<?php

namespace AppBundle\Import;

use AppBundle\Document\Compte as Compte;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;
use Behat\Transliterator\Transliterator;
use AppBundle\Document\CompteTag;
use AppBundle\Manager\CompteManager;

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

        foreach ($csv as $data) {
            $compte = $this->createFromImport($data, $output);
            if ($compte) {
                $this->dm->persist($compte);
            }
            $this->dm->flush();
        }


        $this->dm->flush();
    }

    public function createFromImport($ligne, $output) {
        $prenomNom = trim($ligne[self::CSV_IDENTITE]);
        $nom = substr(strrchr($prenomNom, " "), 1);
        $prenom = trim(str_replace($nom, '', $prenomNom));

        $societeAurouze = $this->dm->getRepository('AppBundle:Societe')->findOneByRaisonSociale("AUROUZE");
        if (!$societeAurouze) {
            $output->writeln(sprintf("<error>La société Aurouze n'a pas été trouvée</error>"));
            return false;
        }
        $compte = $this->dm->getRepository('AppBundle:Compte')->findOneByIdentifiantReprise($ligne[self::CSV_IDENTIFIANT]);
        if (isset($ligne[self::CSV_TYPE])) {
            if (!$compte) {
                
                $tag = new CompteTag();
                $tag->setIdentifiant($ligne[self::CSV_TYPE]);
                $tag->setNom(CompteManager::$tagsCompteLibelles[$ligne[self::CSV_TYPE]]);
                $this->dm->persist($tag);


                $compte = new Compte($societeAurouze);
                $compte->setIdentifiantReprise($ligne[self::CSV_IDENTIFIANT]);
                $compte->setNom($nom);
                $compte->setPrenom($prenom);
                $compte->setCouleur($this->random_color());
                $compte->addTag($tag);

                return $compte;
            } else {
                $tag = new CompteTag();
                $tag->setIdentifiant($ligne[self::CSV_TYPE]);
                $tag->setNom(CompteManager::$tagsCompteLibelles[$ligne[self::CSV_TYPE]]);
                $this->dm->persist($tag);
                $compte->addTag($tag);
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

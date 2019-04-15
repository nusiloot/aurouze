<?php

namespace AppBundle\Import;

use AppBundle\Document\Compte;
use AppBundle\Document\ContactCoordonnee;
use AppBundle\Document\Adresse;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;
use Behat\Transliterator\Transliterator;
use AppBundle\Document\CompteTag;
use AppBundle\Manager\CompteManager;
use Symfony\Component\Console\Helper\ProgressBar;

class ContactCsvImporter extends CsvFile {

    protected $dm;

    const CSV_IDENTIFIANT_REPRISE_ADDRESSE = 0;
    const CSV_IDENTIFIANT_REPRISE_CONTACT = 1;
    const CSV_CIVILITE = 2;
    const CSV_PRENOM = 3;
    const CSV_NOM = 4;
    const CSV_TITRE = 11;
    const CSV_TELEPHONE_FIXE = 12;
    const CSV_TELEPHONE_PORTABLE = 13;
    const CSV_FAX = 14;
    const CSV_EMAIL = 15;
    const CSV_ACTIF = 17;
    const CSV_IDENTIFIANT_REPRISE_SOCIETE = 22;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();

        $i = 0;
        $cptTotal = 0;

        $progress = new ProgressBar($output, 100);
        $progress->start();

        foreach ($csv as $data) {


            $this->createContactFromImport($data, $output);


            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 1000) {
                $this->dm->flush();
                $this->dm->clear();
                gc_collect_cycles();
                $i = 0;
            }
        }

        $this->dm->flush();
    }

    public function createContactFromImport($ligne, $output) {

        $identifiantRepriseEtablissement = $ligne[self::CSV_IDENTIFIANT_REPRISE_ADDRESSE];
        $identifiantRepriseAdresseSociete = $ligne[self::CSV_IDENTIFIANT_REPRISE_ADDRESSE];
        $identifiantRepriseSociete = $ligne[self::CSV_IDENTIFIANT_REPRISE_SOCIETE];
        $etablissement = $this->dm->getRepository('AppBundle:Etablissement')->findOneByIdentifiantReprise($identifiantRepriseEtablissement);
        $societe = null;
        if($etablissement){
            $societe = $etablissement->getSociete();
        }

        if (!$societe) {
            $societe = $this->dm->getRepository('AppBundle:Societe')->findOneByIdentifiantAdresseReprise($identifiantRepriseAdresseSociete);
            if(!$societe){
              $societe = $this->dm->getRepository('AppBundle:Societe')->findOneByIdentifiantReprise($identifiantRepriseSociete);
            }
        }

        if (!$societe) {
            $output->writeln(sprintf("\n<error>La société d'identifiant de reprise %s n'a pas été trouvée (etb? = %s)</error>", $identifiantRepriseSociete,$identifiantRepriseEtablissement));
            return false;
        }

        $compte = new Compte($societe);
        $compte->setSociete($societe);
        $compte->setIdentifiantReprise($ligne[self::CSV_IDENTIFIANT_REPRISE_CONTACT]);
        $compte->setCivilite($this->getCivilite($ligne[self::CSV_CIVILITE]));

        $compte->setPrenom($ligne[self::CSV_PRENOM]);
        $compte->setNom($ligne[self::CSV_NOM]);
        $compte->setIdentite($compte->getIdentite());

        $compte->setTitre($this->getTitre($ligne[self::CSV_TITRE]));

        $contactCoordonnee = new ContactCoordonnee();

        $contactCoordonnee->setTelephoneFixe($ligne[self::CSV_TELEPHONE_FIXE]);
        $contactCoordonnee->setTelephoneMobile($ligne[self::CSV_TELEPHONE_PORTABLE]);
        $contactCoordonnee->setFax($ligne[self::CSV_FAX]);
        $contactCoordonnee->setEmail($ligne[self::CSV_EMAIL]);
        $compte->setContactCoordonnee($contactCoordonnee);
        $compte->setActif(boolval($ligne[self::CSV_ACTIF]));

        $adresse = new Adresse();
        $compte->setAdresse($adresse);
        $this->dm->persist($compte);
        return $compte;
    }

    public function getCivilite($c) {
        if ($c == "1") {
            return CompteManager::CIVILITE_MONSIEUR;
        } elseif ($c == "2") {
            return CompteManager::CIVILITE_MADAME;
        } elseif ($c == "3") {
            return CompteManager::CIVILITE_MADEMOISELLE;
        }
        return "";
    }

    public function getTitre($t) {
        if ($t == "1") {
            return CompteManager::TITRE_MONSIEUR_MAIRE;
        } elseif ($t == "2") {
            return CompteManager::TITRE_MADAME_MAIRE;
        } elseif ($t == "3") {
            return CompteManager::TITRE_MONSIEUR_PRESIDENT_SYNDICAL;
        } elseif ($t == "4") {
            return CompteManager::TITRE_MADAME_PRESIDENTE_SYNDICAL;
        } elseif ($t == "5") {
            return CompteManager::TITRE_MONSIEUR_DIRECTEUR;
        } elseif ($t == "6") {
            return CompteManager::TITRE_MADAME_DIRECTEUR;
        }
        return "";
    }

}

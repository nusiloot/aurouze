<?php

namespace AppBundle\Import;

use AppBundle\Document\Configuration;
use AppBundle\Document\Produit;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationProduitCsvImporter extends CsvFile {

    protected $dm;

    const CSV_ID = 0;
    const CSV_NOM = 1;
    const CSV_CONDITIONNEMENT = 2;
    const CSV_PRIX_HT = 3;
    const CSV_PRIX_PRESTATION = 4;
    const CSV_PRIX_VENTE = 5;
    const CSV_STATUT = 6;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();
        $cpt = 0;
        $configuration = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration();
        if (!$configuration) {
            $configuration = new Configuration();
            $configuration->setId(Configuration::PREFIX);
        }
        foreach ($csv as $data) {
            $produit = new Produit();
            $produit->setNom($data[self::CSV_NOM]);
            $produit->setConditionnement($data[self::CSV_CONDITIONNEMENT]);
            $produit->setPrixHt($data[self::CSV_PRIX_HT]);
            $produit->setPrixPrestation($data[self::CSV_PRIX_PRESTATION]);
            $produit->setPrixVente($data[self::CSV_PRIX_VENTE]);
            if ($data[self::CSV_STATUT]) {
                $produit->setStatut(Produit::PRODUIT_ACTIF);
            } else {
                $produit->setStatut(Produit::PRODUIT_INACTIF);
            }
            $configuration->addProduit($produit);
            $this->dm->persist($configuration);
        }
        $this->dm->flush();
    }

}

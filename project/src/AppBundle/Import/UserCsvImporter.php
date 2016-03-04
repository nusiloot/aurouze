<?php
namespace AppBundle\Import;
use AppBundle\Document\User as User;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\EtablissementManager as EtablissementManager;

class UserCsvImporter extends CsvFile {

    protected $dm;

    const CSV_IDENTIFIANT_USER = 0;
    const CSV_IDENTITE = 1;
    const CSV_NOM = 2;
    const CSV_PRENOM = 3;
    const CSV_COULEUR = 4;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();
        $cpt = 0;
        foreach ($csv as $data) {
            $user = $this->createFromImport($data);
            $this->dm->persist($user);
            if ($cpt > 1000) {
                $this->dm->flush();
                $cpt = 0;
            }
            $cpt++;
        } 
        $this->dm->flush();
    }

    public function createFromImport($ligne) {

        $user = new User();

        $user->setIdentifiant($ligne[self::CSV_IDENTIFIANT_USER]);
        $user->setId();
        $user->setIdentite($ligne[self::CSV_IDENTITE]);
        $user->setNom($ligne[self::CSV_NOM]);
        $user->setPrenom($ligne[self::CSV_PRENOM]);
        $user->setCouleur($ligne[self::CSV_COULEUR]);
        return $user;
    }

}

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

class ContratChangeSocieteCommand extends ContainerAwareCommand {

    protected $societes = array();
    protected $etablissements = array();

    protected function configure() {
        $this
                ->setName('contrat:change-societe')
                ->setDescription('Permet de changer le contrat de societe');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

      $dialog = $this->getHelperSet()->get('dialog');

      $contratId = $dialog->ask($output, 'Identifiant complet du contrat dont la société change (CONTRAT-XXXXXX-XXXXXXXX-XXXX) ? ');
      $output->writeln("Le contrat dont la société change est ".$contratId."\n");

      $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
      $cm = $this->getContainer()->get('contrat.manager');
      $fm = $this->getContainer()->get('facture.manager');
      $sm = $this->getContainer()->get('societe.manager');
      $etbm = $this->getContainer()->get('etablissement.manager');
      $contrat = $cm->getRepository()->find($contratId);
      if(!$contrat){
        $output->writeln("Ce contrat n'existe pas dans la base ");
        exit;
      }else{
        $societeContrat = $contrat->getSociete();
        $passagesContrat = $contrat->getContratPassages();
        $etablissementsArr = array();
        foreach ($passagesContrat as $etbId => $passagesEtb) {
          $etablissementsArr[$etbId] = $etbm->getRepository()->find($etbId);
        }
        foreach ($contrat->getEtablissements() as $etab) {
          $etablissementsArr[$etab->getId()] = $etab;
        }
        $descr = "Ce contrat s'applique à la société ".$societeContrat->getId()." ".$societeContrat->getRaisonSociale()." et aux établissements :";
        foreach ($etablissementsArr as $etablissement) {
          $descr.="\n".$etablissement->getId()." **** ".$etablissement->getIntitule()." ****";
        }
        $output->writeln($descr);

        $factures = $fm->getRepository()->findAllByContrat($contrat);
        foreach ($factures as $facture) {
          $output->writeln("Facture associée ".$facture->getId());
        }
        $output->writeln("");
        $nouvelleSocieteId = $dialog->ask($output, 'Nouvelle Société (SOCIETE-XXXXXX) ? ');
        $output->writeln("\nLa nouvelle société est ".$nouvelleSocieteId);
        $nouvelleSociete = $sm->getRepository()->find($nouvelleSocieteId);
        if(!$nouvelleSociete){
          $output->writeln("Cette société n'existe pas dans la base ");
          exit;
        }
        $this->societes[$societeContrat->getId()] = $nouvelleSocieteId;

        $descr = "La société $nouvelleSocieteId a les établissements suivants : ";
        foreach ($nouvelleSociete->getEtablissements() as $etablissement) {
          if(!$etablissement->getActif()){
            continue;
          }
          $descr.="\n".$etablissement->getId()." **** ".$etablissement->getIntitule()." ****";
        }
        $output->writeln($descr);
        $output->writeln("");

        foreach ($etablissementsArr as $etbSrc) {
          $newEtbId = $dialog->ask($output, $etbSrc->getId().' **** Ou vont les passages de cet Etb ? ');
          $this->etablissements[$etbSrc->getId()] = $newEtbId;
        }

        $output->writeln("");
        $output->writeln("Résumé : déplacement du contrat ".$contrat->getId());
        foreach ($this->societes as $oldId => $newId) {
          $output->writeln($oldId." =>  ".$newId);
        }
        foreach ($this->etablissements as $oldId => $newId) {
            $output->writeln($oldId." =>  ".$newId);
        }
        $output->writeln("");
        $res = $dialog->ask($output, 'OK ? (tape "y")');
        if($res == 'y'){
            //Changement Soc
            $contrat->setSociete($nouvelleSociete);


            //Changement ETBS
            foreach ($contrat->getEtablissements() as $oldEtb) {
              $etbN = $etbm->getRepository()->find($this->etablissements[$oldEtb->getId()]);
              $contrat->addEtablissement($etbN);
            }
            foreach ($etablissementsArr as $oldEtb) {
              $contrat->removeEtablissement($oldEtb);
            }

            foreach ($contrat->getContratPassages() as $oldId => $contratPassages) {
              $etbN = $etbm->getRepository()->find($this->etablissements[$oldId]);
              foreach ($contratPassages->getPassages() as $passage) {
                $passage->setEtablissementIdentifiant($this->etablissements[$oldId]);
                $passage->setEtablissement($etbN);
              }

              $contrat->removeContratPassage($contratPassages);
              $contrat->addContratPassage($etbN,$contratPassages);
            }
          $r = $dialog->ask($output, 'Déplacer aussi les factures ? (tape "y")');
          if($r == 'y'){
                foreach ($factures as $facture) {
                  $facture->setSociete($nouvelleSociete);
                }
            }
            $output->writeln("C'est bon");

        }
      }
      $dm->flush();
    }
}

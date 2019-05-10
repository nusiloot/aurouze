. bin/config.inc


SYMFODIR=$(pwd);
DATA_DIR=$TMP;



echo -e "\n****************************************************\n"
echo -e "\n Mis en cohérence des contrats et des passages au sein des contrats : \n";
echo -e "\n I - On vire l'ensemble des passages appartenant aux contrats non acceptés...\n";
echo -e "\n II - On annule l'ensemble des passages des contrats résiliés ayant lieux après la date de résilition...\n";
echo -e "\n III - Recherche du passage le plus avancé dans le temps 'planifié' ou 'réalisé' => tout les passages précédents sont passé en réalisé\n";
echo -e "\n IV - Recherche des contrats dont tout les passages sont réalisé ou résilié => le contrat est passé en statut FINI...\n";
echo -e "\n****************************************************\n";

php app/console update:contrat-update-statut -vvv --no-debug

echo -e "\n****************************************************\n"
echo -e "\n Mis en cohérence des prestations des contrats...\n";
echo -e "\n Parcourt les passages et ajoute les types de prestations dans les contrats...\n";
echo -e "\n Met à jour le nombre de prestations dans les contrats...\n";
echo -e "\n****************************************************\n";

php app/console update:contrat-update-prestation -vvv --no-debug


echo -e "\n****************************************************\n"
echo -e "\n Mis en cohérence des résiliation...\n";
echo -e "\n Met à jour les contrats ayant le même numéro d'archive (le premier 'résilié' résilie les contrats suivants)...\n";
echo -e "\n****************************************************\n";

php app/console update:contrat-update-resiliation -vvv --no-debug


echo -e "\n****************************************************\n"
echo -e "\n Mis en cohérence des techniciens...\n";
echo -e "\n met a jour les techniciens dans les passages...\n";
echo -e "\n****************************************************\n";

php app/console update:passages-update-technicien -vvv --no-debug

echo -e "\n****************************************************\n"
echo -e "\n       ELASTICSEARCH...                \n";
echo -e "\n****************************************************\n";

php app/console fos:elastica:reset;
php app/console fos:elastica:populate;

echo -e "\n****************************************************\n"
echo -e "\n       GESTION des adresses manquantes                \n";
echo -e "\n****************************************************\n";

php app/console update:etablissements-adresses -vvv --no-debug

php app/console update:synchro-passages-adresses -vvv --no-debug

echo -e "\n****************************************************\n"
echo -e "\n  Mis en cohérence des sociétés / etablissements suspendu...  \n";
echo -e "\n****************************************************\n";

php app/console update:societe-update-actif 2014-05-01 -vvv --no-debug

echo -e "\n****************************************************\n"
echo -e "\n I - Tout les passages d'un contrat réalisé deviennent réalisés\n";
echo -e "\n II - Tout les autres passages sont 'A planifier' \n";
echo -e "\n III - Annulation des contrats qui n'ont pas été accepté depuis le 2014-05-01...\n";
echo -e "\n****************************************************\n";

php app/console update:contrat-encours-to-fini-and-passage-enattente-to-aplanifier 2014-05-01 -vvv --no-debug

echo -e "\n****************************************************\n"
echo -e "\n    Ajout des rendez-vous (peuplement calendrier)...\n";
echo -e "\n****************************************************\n";

php app/console update:rendezvous-par-passage -vvv --no-debug

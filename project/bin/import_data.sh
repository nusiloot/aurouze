#!/bin/bash

. bin/config.inc

REMOTE_DATA=$1
IMPORT_TOTAL=$2

SYMFODIR=$(pwd);
DATA_DIR=$TMP;

if test "$REMOTE_DATA"; then
    echo "Récupération de l'archive et copie dans $TMP/DATAS.tar.gz"
    cp $REMOTE_DATA $TMP/DATAS.tar.gz

    echo "Désarchivage de $TMP/DATAS.tar.gz"
    cd $TMP
    echo $TMP;
    tar -zxvf $TMP/DATAS.tar.gz -C $TMP

    rm $TMP/DATAS.tar.gz
fi

cd $SYMFODIR;

##### Récupération des Comptes TUENET #####

echo "Récupération des comptes TUENET"

cat $DATA_DIR/tblUtilisateur.csv | tr -d "\r" | sort -t ";" -k 1,1 > $DATA_DIR/tblUtilisateur.csv.sorted

cat $DATA_DIR/tblUtilisateur.csv.sorted | sed -r 's/(.*)/\1;;AUTRE/g' | grep -Ev "RefUtilisateur;Nom;" > $DATA_DIR/utilisateurAutre.csv

##### Récupération des Techniciens #####

echo "Récupération des techniciens"

cat $DATA_DIR/tbzTechnicien.csv | tr -d "\r" | sed -r 's/([0-9]+);([0-9]+)/\2;\1/g' | sort -t ";" -k 1,1 > $DATA_DIR/tbzTechnicien.csv.sorted

join -t ';' -1 1 -2 1 $DATA_DIR/tblUtilisateur.csv.sorted $DATA_DIR/tbzTechnicien.csv.sorted > $DATA_DIR/techniciens.csv.tmp

cat $DATA_DIR/techniciens.csv.tmp | sed -r 's/(.*)/\1;TECHNICIEN/g' > $DATA_DIR/techniciens.csv


##### Récupération des Commerciaux #####

echo "Récupération des commerciaux"

cat $DATA_DIR/tbzCommercial.csv | tr -d "\r" | sed -r 's/([0-9]+);([0-9]+)/\2;\1/g' | sort -t ";" -k 1,1 > $DATA_DIR/tbzCommercial.csv.sorted

join -t ';' -1 1 -2 1 $DATA_DIR/tblUtilisateur.csv.sorted $DATA_DIR/tbzCommercial.csv.sorted > $DATA_DIR/commerciaux.csv.tmp

cat $DATA_DIR/commerciaux.csv.tmp | sed -r 's/(.*)/\1;COMMERCIAL/g' > $DATA_DIR/commerciaux.csv

##### Récupération des types de prestations #####

echo "Récupération des types de prestations"

cat $DATA_DIR/vuePrestationType.csv | tr -d "\r" | grep -v "RefPrestationType;" | awk -F ';'  '{
id=$1;
nom=$2;
if($3 != ""){
    nom=nom " - " $3;
}
if($4 != ""){
    nom=nom " - " $4;
}
if($5 != ""){
    nom=nom " - " $5;
}

print $1 ";" $2 ";" $3 ";" $4 ";" $5 ";" nom;

}' > $DATA_DIR/prestationType.csv

cat $DATA_DIR/prestations_utilises.csv | tr -d '"' | awk -F ';' '{ print $1 ";" $2 }' | sed -r 's|(.+);(.+)|\s/\1#\/\2#\/|g' | grep -E "^s/" > $DATA_DIR/sed_prestations_utilises


cat $DATA_DIR/prestationType.csv | sort -t ";" -k 1,1 > $DATA_DIR/prestationType.sorted.csv

##### Récupération des types de produits #####

echo "Récupération des types de produits"

cat $DATA_DIR/tblProduit.csv | tr -d "\r" | grep -v "RefProduit;" | awk -F ';'  '{
print $1 ";" $2 ";" $5 ";" $6 ";" $7 ";" $8";"$10
}' > $DATA_DIR/produits.csv

cat $DATA_DIR/produits.csv | sort -t ";" -k 1,1 > $DATA_DIR/produits.sorted.csv

#### Récupération des Societe ####

echo "Récupération des sociétés"

# gère les retours charriots dans les champs
cat  $DATA_DIR/tblEntite.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sed -r "s|(#)([0-9]+;[0-9]+)|\n\2|g" | sort -t ";" -k 1,1 | sed -r 's|;"([a-zA-Z]{3,5} [0-9]{2} [0-9]{4} [0-9]{2}\:[0-9]{2}\:[0-9]{2}\:[0-9]{3}[a-zA-Z]{2})"|;\1|g' | sed -r 's|;"(!;);(!;)";|;"\1 \2";|' > $DATA_DIR/entite.csv.temp


# Gère les retours charriots dans les champs
cat  $DATA_DIR/tblAdresse.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sed -r "s|(#)([0-9]+;[0-9]+)|\n\2|g" | sort -t ";" -k 2,2 | sed -r 's|;"([a-zA-Z]{3,5} [0-9]{2} [0-9]{4} [0-9]{2}\:[0-9]{2}\:[0-9]{2}\:[0-9]{3}[a-zA-Z]{2})"|;\1|g' | sed -r 's|;"(!;);(!;)";|;"\1 \2";|' > $DATA_DIR/adresse.csv.temp

cat $DATA_DIR/adresse.csv.temp | grep -E "^[0-9]+;[0-9]+;1;" > $DATA_DIR/adresse_facturation.csv

join -a 2 -t ';' -1 2 -2 1 -o auto $DATA_DIR/adresse_facturation.csv $DATA_DIR/entite.csv.temp | grep -v '^"RefEntite"' | sort -n -k 1,1 > $DATA_DIR/societes_doublonnees.csv

cat $DATA_DIR/societes_doublonnees.csv | awk -F ';' '{
    if($3 != "1") {
        next;
    }

    print $0;
}' | grep -Ev "RefEntite;;" | sort -n -k 1,1 > $DATA_DIR/societes.csv

while read line
do
   IDENTIFIANT=`echo $line | cut -d ';' -f 1`;
   PRESENT=`grep "^$IDENTIFIANT;" $DATA_DIR/societes.csv`;
   if [ -z "$PRESENT" ]
   then
    echo $line >> $DATA_DIR/societes.csv
   fi
done < $DATA_DIR/societes_doublonnees.csv;

##TODO : unicité sur les adresses maintenant !!!!!

##### Récupération des Etablissements #####

echo "Récupération des établissements"

#Adresses Application
cat $DATA_DIR/adresse.csv.temp | grep -e "^[0-9]*;[0-9]*;3" > $DATA_DIR/adresse_application.csv

join -t ';' -1 2 -2 1 $DATA_DIR/adresse_application.csv $DATA_DIR/entite.csv.temp > $DATA_DIR/etablissements.csv.tmp

cat $DATA_DIR/etablissements.csv.tmp | awk -F ';' '{
cmt=$15
gsub(/\\n/,"#",cmt);
print $0 ";" cmt
}' > $DATA_DIR/etablissementsCmt.csv.tmp

rm $DATA_DIR/etablissements.csv > /dev/null;
touch $DATA_DIR/etablissements.csv;

while read line
do
   IDENTIFIANT=`echo $line | cut -d ';' -f 2`;
   COORDONNEES=`grep "\"$IDENTIFIANT\";" $DATA_DIR/etablissementsCoordonees.csv | cut -d ";" -f 2,3`;
   echo $line";"$COORDONNEES >> $DATA_DIR/etablissements.csv;

done < $DATA_DIR/etablissementsCmt.csv.tmp

##### Création d'un fichier de pivot pour connaître l'adresse de facturation des prestations d'un établissement #####
cat $DATA_DIR/tblPrestationAdresse.csv | sort -t ";" -k 2,2 > $DATA_DIR/prestationAdresse.sorted.csv
cat  $DATA_DIR/tblPrestation.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/#/g' | sed -r 's/~/#/g' | sort -t ";" -k 1,1 > $DATA_DIR/tblPrestation.tmp.cleaned.csv
cat $DATA_DIR/tblPrestation.tmp.cleaned.csv | cut -d ";" -f 1,2,3 | awk -F ';' '{
    if($3) {
        print $1";"$3
    }else{
      print $1";ENTITE"$2
    }
}' | grep -Ev "RefPrestation;" | sort -t ";" -k 1,1 > $DATA_DIR/prestationSocietes.csv

join -t ';' -1 1 -2 2 $DATA_DIR/prestationSocietes.csv $DATA_DIR/prestationAdresse.sorted.csv | cut -d ";" -f 2,4 > $DATA_DIR/prestationSocietesEtbs.csv

cat $DATA_DIR/etablissements.csv | sort -t ";" -k 2,2 > $DATA_DIR/etablissements.csv.sorted.join
cat $DATA_DIR/prestationSocietesEtbs.csv | sort -t ";" -k 2,2 > $DATA_DIR/prestationSocietesEtbs.csv.sorted.join
join -t ';' -1 2 -2 2 -o auto -a 1 $DATA_DIR/etablissements.csv.sorted.join $DATA_DIR/prestationSocietesEtbs.csv.sorted.join | sort | uniq > $DATA_DIR/etablissements.csv

cat $DATA_DIR/etablissements.csv | sed -r 's|;"([a-zA-Z]{3,5} [0-9]{2} [0-9]{4} [0-9]{2}\:[0-9]{2}\:[0-9]{2}\:[0-9]{3}[a-zA-Z]{2})"|;\1|g' | sed -r 's|;"(!;);(!;)";|;"\1 \2";|' > $DATA_DIR/etablissements.csv.tmp2
mv $DATA_DIR/etablissements.csv{.tmp2,}

##### Récupération des Comptes #####

echo "Récupération des comptes"

cat $DATA_DIR/tbmContactAdresse.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sed -r "s|(#)([0-9]+;[0-9]+)|\n\2|g" > $DATA_DIR/tbmContactAdresse.csv.cleaned
cat $DATA_DIR/tbmContactAdresse.csv.cleaned | sort -t ';' -k 2,2 > $DATA_DIR/contactAdresse.sorted.csv

cat $DATA_DIR/tblContact.csv  | tr -d "\r" | sort -t ';' -k 1,1 > $DATA_DIR/contact.sorted.csv

join -t ';' -1 1 -2 2 $DATA_DIR/contact.sorted.csv $DATA_DIR/contactAdresse.sorted.csv > $DATA_DIR/contactsAdresse.csv

cat $DATA_DIR/contactsAdresse.csv | sort -t ';' -k 11,11 > $DATA_DIR/contactsAdresse.sorted.csv
cat $DATA_DIR/adresse.csv.temp | sort -t ';' -k 1,1 > $DATA_DIR/adresseForCompte.sorted.csv

join -t ';' -1 11 -2 1 $DATA_DIR/contactsAdresse.sorted.csv $DATA_DIR/adresseForCompte.sorted.csv > $DATA_DIR/contacts.csv


cat $DATA_DIR/tblPassageAdresse.csv | tr -d '\r' | sort -t ";" -k 2,2 > $DATA_DIR/passageAdresse.sorted.csv


echo "Import des types de prestations"

php app/console importer:csv configurationPrestation.importer $DATA_DIR/prestations_utilises.csv -vvv --no-debug

echo "Import des types de produits"

php app/console importer:csv configurationProduit.importer $DATA_DIR/produits.csv -vvv --no-debug

echo "Import des sociétés"
echo "0;0;1;TUENET; 176 bis Avenue de la République;;94700;Maisons-Alfort;1;;;;;;;;1;Jan 12 2012 12:02:10:327PM;1;;;;1;TUENET;;0;;1;;1;;;;Jan 12 2012 12:02:09:843PM;;;;0" >> $DATA_DIR/societes.csv
php app/console importer:csv societe.importer $DATA_DIR/societes.csv -vvv --no-debug

echo "Import des etablissements"

php app/console importer:csv etablissement.importer $DATA_DIR/etablissements.csv -vvv --no-debug

echo "Import des commerciaux"

php app/console importer:csv compte.importer $DATA_DIR/commerciaux.csv -vvv --no-debug

echo "Import des techniciens"

php app/console importer:csv compte.importer $DATA_DIR/techniciens.csv -vvv --no-debug

echo "Import Utilisateurs Autres"

php app/console importer:csv compte.importer $DATA_DIR/utilisateurAutre.csv -vvv --no-debug

echo "Import des comptes"

php app/console importer:csv contact.importer $DATA_DIR/contacts.csv -vvv --no-debug

#### Récupération et import des contrats ####

. bin/import_data_contrats.sh


#### Récupération et import des passages ####

. bin/import_data_passages.sh

echo -e "\n****************************************************\n"
echo -e "\nMis en cohérence des contrats et passages...\n";
echo -e "\n****************************************************\n";

php app/console update:contrat-update-statut -vvv --no-debug

echo -e "\n****************************************************\n"
echo -e "\nMis en cohérence des techniciens...\n";
echo -e "\n****************************************************\n";

php app/console update:passages-update-technicien -vvv --no-debug

echo -e "\n****************************************************\n"
echo -e "\nMis en cohérence des prestations...\n";
echo -e "\n****************************************************\n";

php app/console update:contrat-update-prestation -vvv --no-debug


echo -e "\n****************************************************\n"
echo -e "\nMis en cohérence des résiliation...\n";
echo -e "\n****************************************************\n";


php app/console update:contrat-update-resiliation -vvv --no-debug

#### Récupération et import des factures ####

. bin/import_data_factures.sh

#### Récupération et import des reglements ####

. bin/import_data_paiements.sh


echo -e "\n****************************************************\n"
echo -e "\n       ELASTICSEARCH...                  \n";
echo -e "\n****************************************************\n";

php app/console fos:elastica:populate;

echo -e "\n****************************************************\n"
echo -e "\n       GESTION des adresses                  \n";
echo -e "\n****************************************************\n";

php app/console update:etablissements-adresses -vvv --no-debug

php app/console update:synchro-passages-adresses -vvv --no-debug

echo -e "\n****************************************************\n"
echo -e "\n  Mis en cohérence des sociétés / etablissements...  \n";
echo -e "\n****************************************************\n";

php app/console update:societe-update-actif -vvv --no-debug

php app/console update:rendezvous-par-passage -vvv --no-debug

php app/console fos:elastica:populate;

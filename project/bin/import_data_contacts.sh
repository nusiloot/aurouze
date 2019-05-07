#!/bin/bash

. bin/config.inc


SYMFODIR=$(pwd);
DATA_DIR=$TMP;


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

cat $DATA_DIR/etablissementsCmt.csv.tmp | sed -r 's|;"([a-zA-Z]{3,5} [0-9]{2} [0-9]{4} [0-9]{2}\:[0-9]{2}\:[0-9]{2}\:[0-9]{3}[a-zA-Z]{2})"|;\1|g' | sed -r 's|;"(!;);(!;)";|;"\1 \2";|' | sed "s|Afin de garantir un meilleur traitement de vos factures, nous vous demandons d'inscrire, sur chacune d'elles, nos n° d'ordres de service ; nA l'avenir, nous vous demandons d'exiger de la part de nos équipes un ordre de service écrit et numéroté avant toute intervention ; nA défaut de n° d'OS, nous ne pourrons plus, à présent, procéder au moindre règlement ;|Afin de garantir un meilleur traitement de vos factures, nous vous demandons d'inscrire, sur chacune d'elles, nos n° d'ordres de service nA l'avenir, nous vous demandons d'exiger de la part de nos équipes un ordre de service écrit et numéroté avant toute intervention nA défaut de n° d'OS, nous ne pourrons plus, à présent, procéder au moindre règlement |g" > $DATA_DIR/etablissements.csv.proper

rm $DATA_DIR/etablissements.csv > /dev/null;
touch $DATA_DIR/etablissements.csv;

cat $DATA_DIR/etablissementsCoordonnees.csv | cut -d ";" -f 1,2,3 | sort | uniq > $DATA_DIR/etablissementsCoordonnees.uniq.csv

while read line
do
   IDENTIFIANT=`echo $line | cut -d ';' -f 2`;
   COORDONNEES=`grep "^$IDENTIFIANT;" $DATA_DIR/etablissementsCoordonnees.uniq.csv | cut -d ";" -f 2,3`;
   echo $line";"$COORDONNEES >> $DATA_DIR/etablissements.csv;

done < $DATA_DIR/etablissements.csv.proper

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

echo  -e "\nImport des sociétés"
echo "0;0;1;TUENET; 176 bis Avenue de la République;;94700;Maisons-Alfort;1;;;;;;;;1;Jan 12 2012 12:02:10:327PM;1;;;;1;TUENET;;0;;1;;1;;;;Jan 12 2012 12:02:09:843PM;;;;0" >> $DATA_DIR/societes.csv
php app/console importer:csv societe.importer $DATA_DIR/societes.csv -vvv --no-debug

echo  -e "\nImport des etablissements"

php app/console importer:csv etablissement.importer $DATA_DIR/etablissements.csv -vvv --no-debug

echo -e "\nImport des commerciaux"

php app/console importer:csv compte.importer $DATA_DIR/commerciaux.csv -vvv --no-debug

echo -e "\nImport des techniciens"

php app/console importer:csv compte.importer $DATA_DIR/techniciens.csv -vvv --no-debug

echo -e "\nImport Utilisateurs Autres"

php app/console importer:csv compte.importer $DATA_DIR/utilisateurAutre.csv -vvv --no-debug

echo -e "\nImport des comptes"

php app/console importer:csv contact.importer $DATA_DIR/contacts.csv -vvv --no-debug

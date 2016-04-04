#!/bin/bash

. bin/config.inc

REMOTE_DATA=$1

SYMFODIR=$(pwd);
DATA_DIR=$TMP/AUROUZE_DATAS

if test "$REMOTE_DATA"; then
    echo "Récupération de l'archive"
    scp $REMOTE_DATA:AUROUZE_DATAS.tar.gz $TMP/AUROUZE_DATAS.tar.gz

    echo "Désarchivage"
    cd $TMP
    tar zxvf $TMP/AUROUZE_DATAS.tar.gz

    rm $TMP/AUROUZE_DATAS.tar.gz

    cd $SYMFODIR
fi

##### Récupération des Users #####

echo "Récupération des users"

cat $DATA_DIR/tblUtilisateur.csv | tr -d "\r" | sort -t ";" -k 1,1 > $DATA_DIR/tblUtilisateur.csv.sorted

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

#### Récupération des Societe ####

echo "Récupération des sociétés"

# gère les retours charriots dans les champs
cat  $DATA_DIR/tblEntite.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 1,1 > $DATA_DIR/entite.csv.temp

# Gère les retours charriots dans les champs
cat  $DATA_DIR/tblAdresse.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 2,2 > $DATA_DIR/adresse.csv.temp

cat $DATA_DIR/adresse.csv.temp | grep -e "^[0-9]*;[0-9]*;1" > $DATA_DIR/adresse_facturation.csv

join -t ';' -1 2 -2 1 $DATA_DIR/adresse_facturation.csv $DATA_DIR/entite.csv.temp > $DATA_DIR/societes.csv

##### Récupération des Etablissements #####

echo "Récupération des établissements"

#Adresses Application
cat $DATA_DIR/adresse.csv.temp | grep -e "^[0-9]*;[0-9]*;3" > $DATA_DIR/adresse_application.csv

join -t ';' -1 2 -2 1 $DATA_DIR/adresse_application.csv $DATA_DIR/entite.csv.temp > $DATA_DIR/etablissements.csv.tmp

rm $DATA_DIR/etablissements.csv;
touch $DATA_DIR/etablissements.csv;

while read line
do
   IDENTIFIANT=`echo $line | cut -d ';' -f 2`;
   COORDONNEES=`grep "\"$IDENTIFIANT\";" $DATA_DIR/etablissementsCoordonees.csv | cut -d ";" -f 2,3`;
   echo $line";"$COORDONNEES >> $DATA_DIR/etablissements.csv;

done < $DATA_DIR/etablissements.csv.tmp

echo "Récupération des passages"

cat  $DATA_DIR/tblPrestation.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 1,1 > $DATA_DIR/tblPrestation.cleaned.csv

cat $DATA_DIR/tblPassageAdresse.csv | tr -d '\r' | sort -t ";" -k 2,2 > $DATA_DIR/passageAdresse.sorted.csv

# Gère les retours chariots dans les champs
cat  $DATA_DIR/tblPassage.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/^.*RefPassage;/RefPassage;/' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 1,1 > $DATA_DIR/passages.cleaned.sorted.csv

join -t ';' -1 1 -2 2 $DATA_DIR/passages.cleaned.sorted.csv $DATA_DIR/passageAdresse.sorted.csv | sort -t ";" -k 4,4 > $DATA_DIR/passagesadresses.csv

join -t ";" -1 4 -2 1 $DATA_DIR/passagesadresses.csv $DATA_DIR/techniciens.csv | sort -r > $DATA_DIR/passagesadressestechniciens.csv

cat $DATA_DIR/tblPassagePrestationType.csv | tr -d '\r' | grep -v "RefPassagePrestationType;" > $DATA_DIR/tblPassagePrestationType.csv.tmp

rm $DATA_DIR/passagesadressestechniciensprestation.csv;
touch $DATA_DIR/passagesadressestechniciensprestation.csv;

while read line
do
   IDENTIFIANT=`echo $line | cut -d ';' -f 1`;
   PRESTATIONROWS=`grep -E "^[0-9]+;$IDENTIFIANT;" $DATA_DIR/tblPassagePrestationType.csv.tmp | cut -d ";" -f 3`;

   PRESTATIONSVAR="";
   for i in ${PRESTATIONROWS[@]};
   do
      PRESTATION=`grep -E "^$i;" $DATA_DIR/prestationType.csv | cut -d ";" -f 6`;
      PRESTATIONSVAR=$PRESTATION","$PRESTATIONSVAR;
   done
   echo $line";"$PRESTATIONSVAR >> $DATA_DIR/passagesadressestechniciensprestation.csv;

done < $DATA_DIR/passagesadressestechniciens.csv

cat $DATA_DIR/passagesadressestechniciensprestation.csv | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | awk -F ';'  '{
    etablissement_id=sprintf("%06d", $25);
    d=$7;
    d_creation=$19;
    date_passage_debut="";
    if(d) {
        cmd="date --date=\""d"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_passage_debut;
        close(cmd);
    }
    date_creation=$19;

    #if(!date_passage_debut && $6) { "date --date=\"$6\" \"+%Y-%m-%d %H:%M:%S\"" | getline date_passage_debut; }
    #if(!date_passage_debut && $19) { "date --date=\"$19\" \"+%Y-%m-%d %H:%M:%S\"" | getline date_passage_debut; }

    effectue=$13;
    planifie=$18;
    facture=$12;
    imprime=$14;
    duree=$8;

    if(!duree) { duree=60; }

    if(!effectue && date_passage_debut < "2016-01-15 00:00:00") {
        next;
    }

    if(!effectue && date_passage_debut > "2016-03-01 00:00:00") {
        next;
    }

    date_creation=date_passage_debut;

    if(!effectue) {
        date_passage_debut="";
    } else if(effectue && !date_passage_debut) {
        next;
    }

    if(!date_creation) {
        date_creation="2016-01-01 00:00:00";
    }

    libelle=$4;
    type_passage="";

    if($5 == "1") { type_passage="Sous contrat";}
    if($5 == "2") { type_passage="Sous garantie";}
    if($5 == "3") { type_passage="Contrôle"; }
    libelle=libelle " (" type_passage ")";

    description=$17;
    technicien=$26;
    contrat_id=$3;
    prestations=$47;

    if(date_passage_debut && date_passage_debut < "2013-01-01 00:00:00") {
        next;
    }

    print date_creation ";" etablissement_id ";" date_passage_debut ";;" duree ";" technicien ";" libelle ";" description ";" contrat_id ";" prestations

}' > $DATA_DIR/passages.csv

#### Récupération des contrats ####

echo "Récupération des contrats"

cat $DATA_DIR/tblPrestationAdresse.csv | sort -t ";" -k 2,2 > $DATA_DIR/prestationAdresse.sorted.csv

join -t ';' -1 2 -2 1 $DATA_DIR/prestationAdresse.sorted.csv $DATA_DIR/tblPrestation.cleaned.csv > $DATA_DIR/prestation.tmp.csv

cat $DATA_DIR/prestation.tmp.csv | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | awk -F ';'  '{
    contrat_id=$1;
    societe_id=sprintf("%06d", $5);
    etablissement_id=sprintf("%06d", $3);
    commercial_id=sprintf("%06d", $8);
    technicien_id=sprintf("%06d", $9);
    contrat_type=$14;
    prestation_type=$16;
    localisation=$17;
    date_contrat=$13;
    date_creation_contrat="";
    if(date_contrat) {
        cmd="date --date=\""date_contrat"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_creation_contrat;
        close(cmd);
    }
    date_acceptation=$19;
    if(!date_creation_contrat){
        cmd="date --date=\""date_acceptation"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_creation_contrat;
        close(cmd);
    }

    date_debut=$20;
    date_debut_contrat="";
    if(date_debut) {
        cmd="date --date=\""date_debut"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_debut_contrat;
        close(cmd);
    }

    if(date_debut_contrat < "2012-01-01 00:00:00") {
        next;
    }

    duree=$21;
    garantie=$33;
    prixht=$29;
    print contrat_id";"etablissement_id";"societe_id";"commercial_id";"technicien_id";"contrat_type";"prestation_type";"localisation";"date_creation_contrat";"date_debut_contrat";"duree";"garantie";"prixht";";
}' > $DATA_DIR/contrats.csv;


echo "Import des commerciaux"

php app/console importer:csv user.importer $DATA_DIR/commerciaux.csv

echo "Import des techniciens"

php app/console importer:csv user.importer $DATA_DIR/techniciens.csv

echo "Import des types de prestations"
php app/console importer:csv configurationPrestation.importer $DATA_DIR/prestationType.csv

echo "Import des sociétés"

php app/console importer:csv societe.importer $DATA_DIR/societes.csv

echo "Import des etablissements"

php app/console importer:csv etablissement.importer $DATA_DIR/etablissements.csv

echo "Import des passages"

php app/console importer:csv passage.importer $DATA_DIR/passages.csv

echo "Import des contrats"

php app/console importer:csv contrat.importer $DATA_DIR/contrats.csv

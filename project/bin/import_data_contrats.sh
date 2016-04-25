#!/bin/bash

. bin/config.inc

SYMFODIR=$(pwd);
DATA_DIR=$TMP/AUROUZE_DATAS

echo -e "\n\nRécupération des contrats"

cat $DATA_DIR/tblPrestationAdresse.csv | sort -t ";" -k 2,2 > $DATA_DIR/prestationAdresse.sorted.csv


cat $DATA_DIR/tblPrestation.cleaned.csv | grep -v "RefPrestation;RefEntite;" | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | awk -F ';'  '{
    contrat_id=$1;
    societe_old_id=$2;
    contrat_archivage=$8;
    commercial_id=$5;
    technicien_id=$7;
    if(!technicien_id){
        technicien_id=$5;
    }
    contrat_type=$11;
    prestation_type=$13;
    localisation=$14;
    gsub(/\\n/,"#",localisation);

    date_contrat=$10;
    date_creation_contrat="";
    if(date_contrat) {
        cmd="date --date=\""date_contrat"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_creation_contrat;
        close(cmd);
    }
    date_acceptation=$16;
    if(!date_creation_contrat){
        cmd="date --date=\""date_acceptation"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_creation_contrat;
        close(cmd);
    }
    if(!date_creation_contrat){
        next;
    }
    if(date_creation_contrat < "2013-01-01"){
        next;
    }

    date_debut=$17;
    date_debut_contrat="";
    if(date_debut) {
        cmd="date --date=\""date_debut"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_debut_contrat;
        close(cmd);
    }

    date_resiliation=$20;
    date_resiliation_contrat="";
    if(date_resiliation) {
        cmd="date --date=\""date_resiliation"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_resiliation_contrat;
        close(cmd);
    }

    duree=$18;
    garantie=$30;
    prixht=$26;
    tva_reduite=$27;
    print contrat_id";"societe_old_id";"commercial_id";"technicien_id";"contrat_type";"prestation_type";"localisation";"date_creation_contrat";"date_debut_contrat";"duree";"garantie";"prixht";"contrat_archivage";"tva_reduite";"date_resiliation_contrat;
}' > $DATA_DIR/contrats.csv.tmp;

cat $DATA_DIR/tblPrestationProduit.csv | sort -t ";" -k 2,2 > $DATA_DIR/prestationProduit.sorted.csv

rm $DATA_DIR/contrats.csv > /dev/null;
touch $DATA_DIR/contrats.csv;

while read line
do
   IDENTIFIANTCONTRAT=`echo $line | cut -d ';' -f 1`;

   grep -E "[0-9]+;$IDENTIFIANTCONTRAT;" $DATA_DIR/prestationProduit.sorted.csv | cut -d ';' -f 4,5 > $DATA_DIR/produitContrat.tmp.csv

   cat $DATA_DIR/produitContrat.tmp.csv | sort -t ";" -k 1,1  > $DATA_DIR/produitContrat.tmp.sorted.csv

   PRODUITSVAR=$(join -t ';' -1 1 -2 1 $DATA_DIR/produitContrat.tmp.sorted.csv $DATA_DIR/produits.sorted.csv | cut -d ';' -f 2,3 | sed -r 's/(.+);(.+)/\2~\1/g' | tr "\n" "#")

   IDENTIFIANTTECHNICIEN=`echo $line | cut -d ';' -f 4`;
   NOMTECHNICIEN=$(cat $DATA_DIR/utilisateurAutre.csv | grep -E "^$IDENTIFIANTTECHNICIEN;" | cut -d ';' -f 2);

   IDENTIFIANTCOMMERCIAL=`echo $line | cut -d ';' -f 3`;
   NOMCOMMERCIAL=$(cat $DATA_DIR/utilisateurAutre.csv | grep -E "^$IDENTIFIANTCOMMERCIAL;" | cut -d ';' -f 2);

   echo $line";"$PRODUITSVAR";"$NOMCOMMERCIAL";"$NOMTECHNICIEN >> $DATA_DIR/contrats.csv;

done < $DATA_DIR/contrats.csv.tmp

echo -e "\nImport des contrats"

php app/console importer:csv contrat.importer $DATA_DIR/contrats.csv -vvv --no-debug

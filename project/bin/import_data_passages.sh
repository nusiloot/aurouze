#!/bin/bash

. bin/config.inc

SYMFODIR=$(pwd);
DATA_DIR=$TMP;


echo -e "\n\nRécupération des passages"

# Gère les retours chariots dans les champs

cat  $DATA_DIR/tblPassage.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/^.*RefPassage;/RefPassage;/' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | tr '"' ' ' | sort -t ";" -k 1,1 > $DATA_DIR/passages.cleaned.sorted.csv

join -t ';' -1 1 -2 2 $DATA_DIR/passages.cleaned.sorted.csv $DATA_DIR/passageAdresse.sorted.csv | sort -t ";" -k 4,4 > $DATA_DIR/passagesadresses.csv

join -t ";" -1 4 -2 1 -a 1 -o auto $DATA_DIR/passagesadresses.csv $DATA_DIR/utilisateurAutre.csv | sort -r > $DATA_DIR/passagesadressestechniciens.tmp.csv

cat $DATA_DIR/passagesadressestechniciens.tmp.csv | grep -viE "^RefTechnicien;RefPassage;" | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | awk -F ';'  '{
    date_prevision=$6;
    d=$7;
    date_creation=$19;
    date_passage_prevision="";
    cmt=$17
    gsub(/\\n/,"#",cmt);
    if(date_prevision) {
        cmd="date --date=\""date_prevision"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_passage_prevision;
        close(cmd);
    }else{
        if(d) {
            cmd="date --date=\""d"\" \"+%Y-%m-%d %H:%M:%S\"";
            cmd | getline date_passage_prevision;
            close(cmd);
        }else{
            if(date_creation) {
                cmd="date --date=\""date_creation"\" \"+%Y-%m-%d %H:%M:%S\"";
                cmd | getline date_passage_prevision;
                close(cmd);
            }
        }
    }

    if(!date_passage_prevision) {
        next;
    }
    print $0 ";" cmt ;

}' > $DATA_DIR/passagesadressestechniciens.csv


echo 'NO DEBUG MODE => IMPORT TOTAL '


cat $DATA_DIR/tblPassagePrestationType.csv | tr -d '\r' | grep -v "RefPassagePrestationType;" > $DATA_DIR/tblPassagePrestationType.csv.tmp

cat $DATA_DIR/tblPassageProduit.csv | tr -d '\r' | sort -t ";" -k 2,2 > $DATA_DIR/passageProduit.sorted.csv

cat $DATA_DIR/prestationTypes.tmp.csv | sort -t ";" -k 1,1  > $DATA_DIR/prestationTypes.tmp.sorted.csv

rm $DATA_DIR/passagesadressestechniciensprestation.csv > /dev/null;
touch $DATA_DIR/passagesadressestechniciensprestation.csv;

while read line
do
   IDENTIFIANT=`echo $line | cut -d ';' -f 2`;
   grep -E "^[0-9]+;$IDENTIFIANT;" $DATA_DIR/tblPassagePrestationType.csv.tmp | cut -d ";" -f 3 > $DATA_DIR/prestationTypes.tmp.csv;
   cat $DATA_DIR/prestationTypes.tmp.csv | sort -t ";" -k 1,1  > $DATA_DIR/prestationTypes.tmp.sorted.csv
   PRESTATIONSVAR=$(join -t ';' -1 1 -2 1 $DATA_DIR/prestationTypes.tmp.sorted.csv $DATA_DIR/prestationType.sorted.csv | cut -d ';' -f 6 | tr "\n" "#")


   grep -E "[0-9]+;$IDENTIFIANT;" $DATA_DIR/passageProduit.sorted.csv | cut -d ';' -f 3,5 > $DATA_DIR/passageProduit.tmp.csv
   cat $DATA_DIR/passageProduit.tmp.csv | sort -t ";" -k 1,1  > $DATA_DIR/passageProduit.tmp.sorted.csv

   PRODUITSVAR=$(join -t ';' -1 1 -2 1 $DATA_DIR/passageProduit.tmp.sorted.csv $DATA_DIR/produits.sorted.csv | cut -d ';' -f 2,3 | sed -r 's/(.+);(.+)/\2~\1/g' | tr "\n" "#")

   echo $line";"$PRESTATIONSVAR";"$PRODUITSVAR >> $DATA_DIR/passagesadressestechniciensprestation.csv;

done < $DATA_DIR/passagesadressestechniciens.csv

cat $DATA_DIR/passagesadressestechniciensprestation.csv | sed -f $DATA_DIR/sed_prestations_utilises > $DATA_DIR/passagesadressestechniciensprestation.proper.csv

cat $DATA_DIR/passagesadressestechniciensprestation.proper.csv | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | awk -F ';'  '{
    etablissement_id=$25;
    date_prevision=$6;
    d=$7;
    date_creation=$19;
    date_arrivee=$9;
    date_depart=$10;
    effectue=$13;
    planifie=$18;
    facture=$12;
    imprime=$14;
    duree=$8;
    old_id=$2;


    date_passage_debut="";
    if(d) {
        cmd="date --date=\""d"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_passage_debut;
        close(cmd);
    }

    date_passage_fin="";
    if(date_depart) {
        cmd="date --date=\""date_depart"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_passage_fin;
        close(cmd);
    }

    date_passage_prevision="";
    if(date_prevision) {
        cmd="date --date=\""date_prevision"\" \"+%Y-%m-%d %H:%M:%S\"";
        cmd | getline date_passage_prevision;
        close(cmd);
    }else{
        if(d) {
            cmd="date --date=\""d"\" \"+%Y-%m-%d %H:%M:%S\"";
            cmd | getline date_passage_prevision;
            close(cmd);
        }else{
            cmd="date --date=\""date_creation"\" \"+%Y-%m-%d %H:%M:%S\"";
            cmd | getline date_passage_prevision;
            close(cmd);
        }
    }

    date_passage_creation=date_creation;
#    if(date_creation) {
#        cmd="date --date=\""date_creation"\" \"+%Y-%m-%d %H:%M:%S\"";
#        cmd | getline date_passage_creation;
#        close(cmd);
#    }



    if(!duree) { duree=60; }


    libelle=$4;
    type_passage="";

    if($5 == "1") { type_passage="CONTRAT";}
    if($5 == "2") { type_passage="GARANTIE";}
    if($5 == "3") { type_passage="CONTROLE"; }
    libelle=libelle " (" type_passage ")";

    technicien=$1;
    contrat_id=$3;

    description=$47;
    prestations=$48;
    produits=$49;

     if(effectue){
        if(!date_passage_debut && date_passage_prevision) { date_passage_debut=date_passage_prevision; }
        if(!date_passage_fin && date_passage_prevision) { date_passage_fin=date_passage_prevision; }
        print date_passage_creation ";" etablissement_id ";" date_passage_prevision ";" date_passage_debut ";" date_passage_fin ";" duree ";" technicien ";" libelle ";" description ";" contrat_id ";" effectue ";" prestations ";" produits ";REALISE;"old_id  ";" type_passage ;
    }else{
        if(planifie){
            date_passage_debut=d;
            date_passage_fin="";
            print date_passage_creation ";" etablissement_id ";" date_passage_prevision ";" date_passage_debut ";" date_passage_fin ";" duree ";" technicien ";" libelle ";" description ";" contrat_id ";0;" prestations ";" produits ";PLANIFIE;"old_id ";" type_passage ;
        }else{
            date_passage_debut="";
            date_passage_fin="";
            print date_passage_creation ";" etablissement_id ";" date_passage_prevision ";" date_passage_debut ";" date_passage_fin ";" duree ";" technicien ";" libelle ";" description ";" contrat_id ";0;" prestations ";" produits ";EN_ATTENTE;"old_id ";" type_passage ;
        }
    }

}' > $DATA_DIR/passages.csv


echo -e "\nImport des passages"

php app/console importer:csv passage.importer $DATA_DIR/passages.csv -vvv --no-debug

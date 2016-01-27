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

#####IMPORT des Etablissements ##### 

# Gère les retours charriots dans les champs 
cat  $DATA_DIR/tblAdresse.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 2,2 > $DATA_DIR/adresse.csv.temp

# Gère les retours charriots dans les champs 
cat  $DATA_DIR/tblEntite.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 1,1 > $DATA_DIR/entite.csv.temp

join -t ';' -1 2 -2 1 $DATA_DIR/adresse.csv.temp $DATA_DIR/entite.csv.temp > $DATA_DIR/adresse.csv

php app/console import:data "Etablissement" $DATA_DIR/adresse.csv

#### CREATION PASSAGES.CSV ####
echo "Récupération des passages"

cat $DATA_DIR/tblPassageAdresse.csv | tr -d '\r' | sort -t ";" -k 2,2 > $DATA_DIR/passageAdresse.sorted.csv

# Gère les retours chariots dans les champs 
cat  $DATA_DIR/tblPassage.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/^.*RefPassage;/RefPassage;/' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 1,1 > $DATA_DIR/passages.cleaned.sorted.csv

join -t ';' -1 1 -2 2 $DATA_DIR/passages.cleaned.sorted.csv $DATA_DIR/passageAdresse.sorted.csv | sort -t ";" -k 4,4 > $DATA_DIR/passagesadresses.csv

cat $DATA_DIR/tblUtilisateur.csv | tr -d '\r' | cut -d ";" -f 1,2 | sed 's/^.*RefUtilisateur;/RefTechnicien;/' | sort -t ";" -k 1,1 > $DATA_DIR/techniciens.csv

join -t ";" -1 4 -2 1 $DATA_DIR/passagesadresses.csv $DATA_DIR/techniciens.csv | sort -r > $DATA_DIR/passagesadressestechniciens.csv

cat $DATA_DIR/passagesadressestechniciens.csv | awk -F ';'  '{
    etablissement_id=sprintf("%06d", $25);
    date_passage_debut=$7;
    if(!date_passage_debut) {
        date_passage_debut=$6;
    }
    if(!date_passage_debut) {
        date_passage_debut=$19;
    }
    effectue=$13;
    planifie=$18;
    duree=$8;

    if(!duree) {
        duree=60;
    }

    if(!planifie) {
        next;
    }

    date_creation=date_passage_debut;

    if(!date_creation) {
        next;
    }

    libelle=$4; 
    type_passage="";

    if($5 == "1") {
        type_passage="Sous contrat";
    }

    if($5 == "2") {
        type_passage="Sous garantie";
    }

    if($5 == "3") {
        type_passage="Contrôle";
    }
    libelle=libelle " (" type_passage ")";

    description=$17;
    technicien=$26;

    print date_creation ";" etablissement_id ";" date_passage_debut ";;" duree ";" technicien ";" libelle ";" description

}' > $DATA_DIR/passages.csv


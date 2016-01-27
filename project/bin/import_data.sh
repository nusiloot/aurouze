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

# gère les retours charriots dans les champs 
cat  $DATA_DIR/tblAdresse.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 2,2 > $DATA_DIR/adresse.csv.temp

# gère les retours charriots dans les champs 
cat  $DATA_DIR/tblEntite.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 1,1 > $DATA_DIR/entite.csv.temp

join -t ';' -1 2 -2 1 /tmp/AUROUZE_DATAS/adresse.csv.temp /tmp/AUROUZE_DATAS/entite.csv.temp > $DATA_DIR/adresse.csv

php app/console import:data "Etablissement" $DATA_DIR/adresse.csv

#### CREATION PASSAGES.CSV ####
echo "Récupération de passage.csv"

cat $DATA_DIR/tblPassageAdresse.csv  | sort -t ";" -k 2,2 > $DATA_DIR/passageAdresse.csv.temp

# gère les retours charriots dans les champs 
cat  $DATA_DIR/tblPassage.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sort -t ";" -k 1,1 > $DATA_DIR/passage.csv.temp

join -t ';' -1 1 -2 2 $DATA_DIR/passage.csv.temp $DATA_DIR/passageAdresse.csv.temp


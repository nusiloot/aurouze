#!/bin/bash

echo -e "\n****************************************************\n"
echo -e "\n DEBUT DE L'IMPORT DES PAIEMENTS \n";
echo -e "\n****************************************************\n";


. bin/config.inc

REMOTE_DATA=$1
SYMFODIR=$(pwd);
DATA_DIR=$TMP/AUROUZE_DATAS

if test "$REMOTE_DATA"; then
    echo "Récupération de l'archive"
    scp $REMOTE_DATA:AUROUZE_DATAS_FINAL.tar.gz $TMP/AUROUZE_DATAS.tar.gz

    echo "Désarchivage"
    cd $TMP
    tar zxvf $TMP/AUROUZE_DATAS.tar.gz

    rm $TMP/AUROUZE_DATAS.tar.gz

    cd $SYMFODIR
fi

##### CONSTRUCTION DES FICHIERS... #####

echo -e "\n\nCONSTRUCTION DES FICHIERS...\n"

cat $DATA_DIR/tblReglement.csv | tr -d "\r" | sort -t ';' -k 3,3 | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' > $DATA_DIR/reglements.clean.csv

cat $DATA_DIR/tblPieceBanque.csv | tr -d "\r" | sort -t ';' -k 1,1 | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' > $DATA_DIR/piecesBanques.clean.csv

join -a 1 -t ';' -1 3 -2 1 -o auto $DATA_DIR/reglements.clean.csv $DATA_DIR/piecesBanques.clean.csv | sort -t ';' -k 13,13  > $DATA_DIR/paiementsClean.csv

cat $DATA_DIR/tblRemiseCheque.csv | tr -d "\r" | sort -t ';' -k 1,1 | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' > $DATA_DIR/cheques.clean.csv

join -a 1 -t ';' -1 13 -2 1 $DATA_DIR/paiementsClean.csv $DATA_DIR/cheques.clean.csv > $DATA_DIR/paiements.csv

echo -e "\n\nImport des types de paiements\n"

php app/console importer:csv paiements.importer $DATA_DIR/paiements.csv -vvv

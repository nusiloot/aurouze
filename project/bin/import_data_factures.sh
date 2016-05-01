#!/bin/bash

. bin/config.inc

SYMFODIR=$(pwd);
DATA_DIR=$TMP/AUROUZE_DATAS


echo -e "\n\nRécupération des factures"

# Gère les retours chariots dans les champs
cat  $DATA_DIR/tblFacture.csv | tr "\r" '~' | tr "\n" '#' | grep -Ev "^RefFacture;Numero;" | sed -r 's/~#([0-9]+;[0-9]+;[0-1]{1};)/\n\1/g' | sed -r 's/~#/\\n/g'  | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | sort -t ";" -k 8,8 > $DATA_DIR/factures.cleaned.sorted.csv

join -t ';' -1 8 -2 1 $DATA_DIR/factures.cleaned.sorted.csv $DATA_DIR/contrats.csv > $DATA_DIR/factures.tmp.csv

cat $DATA_DIR/factures.tmp.csv | sort -t ";" -k 2,2 > $DATA_DIR/factures.sorted.csv

cat $DATA_DIR/tblFactureLigne.csv | tr "\r" '~' | tr "\n" '#' | sed -r 's/~#([0-9]+;[0-9]+;)/\n\1/g' | sed -r 's/~#/\\n/g' | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | sort -t ";" -k 2,2 > $DATA_DIR/factureslignes.cleaned.sorted.csv

join -t ';' -1 2 -2 2  $DATA_DIR/factureslignes.cleaned.sorted.csv $DATA_DIR/factures.sorted.csv | grep -Ev "RefFacture(.+)RefFactureLigne" > $DATA_DIR/factures.csv

echo "Import des factures"

php app/console importer:csv facture.importer $DATA_DIR/factures.csv -vvv


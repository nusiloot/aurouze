#!/bin/bash

. bin/config.inc

SYMFODIR=$(pwd);
DATA_DIR=$TMP/AUROUZE_DATAS

echo -e "\n\nRécupération des factures"

# Gère les retours chariots dans les champs
cat  $DATA_DIR/tblFacture.csv | tr -d "\r" | tr "\n" '#' | sed -r 's/#([0-9]+;[0-9]*;[0-9]*;[0-9]*;[0-9]*;[0-9]+;[0-9]*;[0-9]*;)/\n\1/g' | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | sort -t ";" -k 1,1 > $DATA_DIR/factures.cleaned.sorted.csv

cat $DATA_DIR/tblFactureLigne.csv | tr -d "\r" | tr "\n" '#' | sed -r 's/#([0-9]+;[0-9]+;[0-9]*;[0-9]*;)/\n\1/g' | sed -r 's/([a-zA-Z]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9:]+):[0-9]{3}([A-Z]{2})/\1 \2 \3 \4 \5/g' | sort -t ";" -k 2,2 > $DATA_DIR/factureslignes.cleaned.sorted.csv

join -t ';' -1 1 -2 2 $DATA_DIR/factures.cleaned.sorted.csv  $DATA_DIR/factureslignes.cleaned.sorted.csv -o auto | awk -F ';' '{
    if($2 && $8 && ($9 !~ " (2012|2013|2014|2015|2016|2017) " && $2 !~ "^(2012|2013|2014|2015|2016|2017)")) { next }
    if($2 && !$8 && $9 !~ " (2013|2014|2015|2016|2017) " && $2 !~ "^(2013|2014|2015|2016|2017)") { next }
    print $0;
}' | sort -t ";" -n -k 1,1 > $DATA_DIR/factures_complets.csv

echo "Import des factures"

php app/console importer:csv facture.importer $DATA_DIR/factures_complets.csv -vvv --no-debug

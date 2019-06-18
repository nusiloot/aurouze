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

echo  -e "\nImport des types de prestations"

php app/console importer:csv configurationPrestation.importer $DATA_DIR/prestations_utilises.csv -vvv --no-debug

echo  -e "\nImport des types de produits"

php app/console importer:csv configurationProduit.importer $DATA_DIR/produits.csv -vvv --no-debug

echo -e "\nCompilation des fichiers liés aux contacts / établissements et sociétés"

. bin/import_data_contacts.sh


echo -e "\n****************************************************\n"
echo -e "\n    Les etablissements dont le nom est 'Immeuble' deviennent de type Immeuble...\n";
echo -e "\n****************************************************\n";

php app/console etablissements:etablissements-update-type "IMMEUBLE" -vvv --no-debug

#### Récupération et import des contrats ####

. bin/import_data_contrats.sh

#### Récupération et import des passages ####

. bin/import_data_passages.sh

#### Récupération et import des factures ####

. bin/import_data_factures.sh

#### Récupération et import des reglements ####

. bin/import_data_paiements.sh


php app/console fos:elastica:reset;
php app/console fos:elastica:populate;

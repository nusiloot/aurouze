#!/bin/bash

. bin/config.inc

REMOTE_DATA=$1
IMPORT_TOTAL=$2

SYMFODIR=$(pwd);
DATA_DIR=$TMP;

. bin/import_data_brut.sh $REMOTE_DATA $IMPORT_TOTAL

. bin/import_data_verifications.sh

. bin/import_data_post_traitement.sh $REMOTE_DATA $IMPORT_TOTAL

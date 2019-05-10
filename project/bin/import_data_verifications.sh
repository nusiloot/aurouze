#!/bin/bash

. bin/config.inc


SYMFODIR=$(pwd);
DATA_DIR=$TMP;



php import:global-documents-verifications "Societe" $DATA_DIR/societes.csv -vvv --no-debug

#!/bin/bash

DIR=$(dirname ${BASH_SOURCE[0]})

#Fetch exports from DLDB

echo "Usage: $0 /path/to/export/cache"

CACHE=`readlink -e $1`
echo 
echo "Using path: $CACHE"

cp $CACHE/amtshelfer_standorte.json $DIR/../tests/Dldb/fixtures/locations_de.json
cp $CACHE/amtshelfer_dienstleistungen.json $DIR/../tests/Dldb/fixtures/services_de.json
cp $CACHE/amtshelfer_settings.json $DIR/../tests/Dldb/fixtures/settings.json
cp $CACHE/amtshelfer_themen.json $DIR/../tests/Dldb/fixtures/topics_de.json
cp $CACHE/amtshelfer_behoerden.json $DIR/../tests/Dldb/fixtures/authorities_de.json


#!/bin/bash

DIR=$(dirname ${BASH_SOURCE[0]})

#Fetch exports from DLDB

echo "Usage: $0 /path/to/export/cache"

CACHE=`readlink -e $1`
echo 
echo "Using path: $CACHE"

cp $CACHE/amtshelfer_standorte.json $DIR/../tests/Dldb/fixtures/locations_de.json
cp $CACHE/amtshelfer_standorte_en.json $DIR/../tests/Dldb/fixtures/locations_en.json
cp $CACHE/amtshelfer_dienstleistungen.json $DIR/../tests/Dldb/fixtures/services_de.json
cp $CACHE/amtshelfer_dienstleistungen_en.json $DIR/../tests/Dldb/fixtures/services_en.json
cp $CACHE/amtshelfer_settings.json $DIR/../tests/Dldb/fixtures/settings.json
cp $CACHE/amtshelfer_themen.json $DIR/../tests/Dldb/fixtures/topic_de.json
cp $CACHE/amtshelfer_behoerden.json $DIR/../tests/Dldb/fixtures/authoriy_de.json


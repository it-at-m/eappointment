#!/bin/bash

DIR=$(dirname ${BASH_SOURCE[0]})

#Fetch exports from DLDB

echo "Usage: $0 /path/to/export/cache"

CACHE=`readlink -e $1`
echo 
echo "Using path: $CACHE"

cp $CACHE/amtshelfer_standorte.json $DIR/../tests/Zmsdldb/fixtures/locations_de.json
cp $CACHE/amtshelfer_standorte_en.json $DIR/../tests/Zmsdldb/fixtures/locations_en.json
cp $CACHE/amtshelfer_dienstleistungen.json $DIR/../tests/Zmsdldb/fixtures/services_de.json
cp $CACHE/amtshelfer_dienstleistungen_en.json $DIR/../tests/Zmsdldb/fixtures/services_en.json
cp $CACHE/amtshelfer_settings.json $DIR/../tests/Zmsdldb/fixtures/settings.json
cp $CACHE/amtshelfer_themen.json $DIR/../tests/Zmsdldb/fixtures/topic_de.json
cp $CACHE/amtshelfer_behoerden.json $DIR/../tests/Zmsdldb/fixtures/authoriy_de.json


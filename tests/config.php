<?php


define('FIXTURES', __DIR__ . DIRECTORY_SEPARATOR . 'Dldb' . DIRECTORY_SEPARATOR . 'fixtures');
define('LOCATION_SINGLE', 122231); // Abgeordnetenhaus von Berlin
define('SERVICE_SINGLE', 120703); // Personalausweis
define('TOPIC_SINGLE', 324801); // Arbeit, Beruf und Soziales
define('AUTHORITY_SINGLE', 12679); // Abgeordnetenhaus
define('LOCATION_CSV', '122281,122280,122231'); // Rathaus Spandau, Rathaus Mitte, LOCATION_SINGLE
define('SERVICE_CSV', '120703,121151'); // Personalausweis, Reisepass

define('LOCATION_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'locations_de.json');
define('SERVICE_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'services_de.json');
define('TOPICS_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'topic_de.json');
define('SETTINGS_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'settings.json');
define('AUTHORITY_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'authority_de.json');
define('ES_HOST', getenv('ES_HOST') ? getenv('ES_HOST') : 'localhost');
define('ES_PORT', getenv('ES_PORT') ? getenv('ES_PORT') : '9200');
define('ES_TRANSPORT', getenv('ES_TRANSPORT') ? getenv('ES_TRANSPORT') : 'Http');
define('ES_ALIAS', getenv('ES_ALIAS') ? getenv('ES_ALIAS') : 'dldbtest');
define('ES_TEST', false);
define('TEMPLATE_TEST', false);

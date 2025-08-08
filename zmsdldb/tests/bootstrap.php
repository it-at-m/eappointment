<?php

// Load the autoloader
require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .  'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

// Define essential constants for tests
define('FIXTURES', __DIR__ . DIRECTORY_SEPARATOR . 'Zmsdldb' . DIRECTORY_SEPARATOR . 'fixtures');
define('LOCATION_SINGLE', 122231);
define('SERVICE_SINGLE', 120703);
define('TOPIC_SINGLE', 324801);
define('AUTHORITY_SINGLE', 12679);
define('LOCATION_CSV', '122281,122280,122231');
define('SERVICE_CSV', '120703,121151');

define('LOCATION_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'locations_de.json');
define('SERVICE_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'services_de.json');
define('TOPICS_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'topic_de.json');
define('SETTINGS_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'settings.json');
define('AUTHORITY_JSON', FIXTURES . DIRECTORY_SEPARATOR . 'authority_de.json');
define('ES_HOST', 'localhost');
define('ES_PORT', '9200');
define('ES_TRANSPORT', 'Http');
define('ES_ALIAS', 'dldbtest');
define('ES_TEST', false);
define('TEMPLATE_TEST', false);

<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');

class App extends \BO\Zmsmessaging\Application
{
    const APP_PATH = APP_PATH;

    // Uncomment the following lines on debugging
    const DEBUG = false;

    // http curl options
    public static $http_curl_config = array(
        CURLOPT_SSL_VERIFYPEER => false, // Internal certificates are self-signed
        CURLOPT_TIMEOUT => 10,
        // CURLOPT_VERBOSE => true
    );

    const HTTP_BASE_URL = ZMS_API_URL;
}

// uncomment for testing
App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));

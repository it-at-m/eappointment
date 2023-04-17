<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');

define('ZMS_API_PASSWORD_MESSAGING', getenv('ZMS_API_PASSWORD_MESSAGING')
    ? getenv('ZMS_API_PASSWORD_MESSAGING')
    : 'examplepassword');

define('ZMS_API_PROXY', getenv('ZMS_API_PROXY') ? getenv('ZMS_API_PROXY') : NULL);

class App extends \BO\Zmsmessaging\Application
{
    const APP_PATH = APP_PATH;

    // Uncomment the following lines on debugging
    const DEBUG = false;

    /**
     * HTTP access for api
     */
    const HTTP_BASE_URL = ZMS_API_URL;

    public static $httpUser = '_system_messenger';

    public static $httpPassword = ZMS_API_PASSWORD_MESSAGING;

     // http curl options
     public static $http_curl_config = array(
        CURLOPT_SSL_VERIFYPEER => false, // Internal certificates are self-signed
        CURLOPT_TIMEOUT => 10,
        CURLOPT_PROXY => ZMS_API_PROXY,
        // CURLOPT_VERBOSE => true
    );
}

// uncomment for testing
//App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));

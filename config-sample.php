<?php
// @codingStandardsIgnoreFile
class App extends \BO\Zmsmessaging\Application
{

    const APP_PATH = APP_PATH;

    // Uncomment the following lines on debugging
    const DEBUG = TRUE;
    const MONOLOG_LOGLEVEL = \Monolog\Logger::DEBUG;

    // http curl options
    public static $http_curl_config = array(
        CURLOPT_SSL_VERIFYPEER => false, // Internal certificates are self-signed
        CURLOPT_TIMEOUT => 3,
        // CURLOPT_VERBOSE => true
    );

    const HTTP_BASE_URL = 'http://example.com/terminvereinbarung/api/2';
}

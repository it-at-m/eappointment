<?php
// @codingStandardsIgnoreFile

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');

define('ZMS_API_PASSWORD_MESSAGING', getenv('ZMS_API_PASSWORD_MESSAGING')
    ? getenv('ZMS_API_PASSWORD_MESSAGING')
    : 'examplepassword');

define('ZMS_API_PROXY', getenv('ZMS_API_PROXY') ? getenv('ZMS_API_PROXY') : NULL);

define('ZMS_MESSAGING_SMTP_ENABLED', getenv('ZMS_MESSAGING_SMTP_ENABLED') !== false);
define('ZMS_MESSAGING_SMTP_HOST', getenv('ZMS_MESSAGING_SMTP_HOST'));
define('ZMS_MESSAGING_SMTP_PORT', intval(getenv('ZMS_MESSAGING_SMTP_PORT')));
define('ZMS_MESSAGING_SMTP_AUTH_ENABLED', getenv('ZMS_MESSAGING_SMTP_AUTH_ENABLED') !== false);
define('ZMS_MESSAGING_SMTP_AUTH_METHOD', getenv('ZMS_MESSAGING_SMTP_AUTH_METHOD') !== false);
define('ZMS_MESSAGING_SMTP_USERNAME', getenv('ZMS_MESSAGING_SMTP_USERNAME'));
define('ZMS_MESSAGING_SMTP_PASSWORD', getenv('ZMS_MESSAGING_SMTP_PASSWORD'));
define('ZMS_MESSAGING_SMTP_SKIP_TLS_VERIFY', getenv('ZMS_MESSAGING_SMTP_SKIP_TLS_VERIFY') !== false);
define('ZMS_MESSAGING_SMTP_DEBUG', getenv('ZMS_MESSAGING_SMTP_DEBUG') !== false);

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

    /*
     * -----------------------------------------------------------------------
     * SMTP settings
     */
    public static $smtp_enabled = ZMS_MESSAGING_SMTP_ENABLED;
    public static $smtp_host = ZMS_MESSAGING_SMTP_HOST;
    public static $smtp_port = ZMS_MESSAGING_SMTP_PORT;
    public static $smtp_auth_enabled = ZMS_MESSAGING_SMTP_AUTH_ENABLED;
    public static $smtp_auth_method = ZMS_MESSAGING_SMTP_AUTH_METHOD;
    public static $smtp_username = ZMS_MESSAGING_SMTP_USERNAME;
    public static $smtp_password = ZMS_MESSAGING_SMTP_PASSWORD;
    public static $smtp_skip_tls_verify = ZMS_MESSAGING_SMTP_SKIP_TLS_VERIFY;
    public static $smtp_debug = ZMS_MESSAGING_SMTP_DEBUG;
}

// uncomment for testing
//App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));

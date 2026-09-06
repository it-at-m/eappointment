<?php

/**
 *
 * @package Zmsmessaging
 *
 */

namespace BO\Zmsmessaging;

use BO\Zmsclient\Http;

/**
 * @SuppressWarnings("TooManyFields")
 */

if (($token = getenv('ZMS_CONFIG_SECURE_TOKEN')) === false || $token === '') {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const string IDENTIFIER = 'zms';
    const string MODULE_NAME = 'zmsmessaging';

    const bool DEBUG = false;

    /*
     * -----------------------------------------------------------------------
     * ZMS Messaging access
     */

    public static mixed $messaging = null;

    /*
     * -----------------------------------------------------------------------
     * ZMS API access
     */
    public static ?Http $http = null;

    public static string $httpUser = 'test';

    public static string $httpPassword = 'test';

    public static array $http_curl_config = array();

    /**
     * config preferences
     */
    const string CONFIG_SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

    /**
     * HTTP url for api
     */
    const string HTTP_BASE_URL = 'http://user:pass@host.tdl';

    /*
     * -----------------------------------------------------------------------
     * Mail settings
     */
    public static mixed $mails_per_minute = 300;

    /*
     * -----------------------------------------------------------------------
     * SMTP settings
     */
    public static bool $smtp_enabled = false;

    public static mixed $smtp_host = null;

    public static mixed $smtp_port = null;

    public static bool $smtp_auth_enabled = true;

    public static mixed $smtp_auth_method = null;

    public static mixed $smtp_username = null;

    public static mixed $smtp_password = null;

    public static bool $smtp_skip_tls_verify = false;

    public static bool $verify_dns_enabled = false;

    public static mixed $smtp_debug = false;
}

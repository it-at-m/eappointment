<?php

/**
 *
 * @package Zmsmessaging
 *
 */

namespace BO\Zmsmessaging;

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
    const IDENTIFIER = 'zms';
    const MODULE_NAME = 'zmsmessaging';

    const DEBUG = false;

    /*
     * -----------------------------------------------------------------------
     * ZMS Messaging access
     */

    public static $messaging = null;

    /*
     * -----------------------------------------------------------------------
     * ZMS API access
     */
    public static $http = null;

    public static string $httpUser = 'test';

    public static string $httpPassword = 'test';

    /**
     * @var array
     */
    public static array $http_curl_config = array();

    /**
     * config preferences
     */
    const CONFIG_SECURE_TOKEN = ZMS_CONFIG_SECURE_TOKEN;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = 'http://user:pass@host.tdl';

    /*
     * -----------------------------------------------------------------------
     * Mail settings
     */
    public static int $mails_per_minute = 300;

    /*
     * -----------------------------------------------------------------------
     * SMTP settings
     */
    /**
     * @var false
     */
    public static bool $smtp_enabled = false;

    public static $smtp_host = null;

    public static $smtp_port = null;

    /**
     * @var true
     */
    public static bool $smtp_auth_enabled = true;

    public static $smtp_auth_method = null;

    public static $smtp_username = null;

    public static $smtp_password = null;

    /**
     * @var false
     */
    public static bool $smtp_skip_tls_verify = false;

    /**
     * @var false
     */
    public static bool $verify_dns_enabled = false;

    /**
     * @var false
     */
    public static bool $smtp_debug = false;
}

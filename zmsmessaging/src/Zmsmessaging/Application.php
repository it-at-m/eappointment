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

if (!getenv('ZMS_CONFIG_SECURE_TOKEN')) {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

class Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'zms';
    const MODULE_NAME = 'zmsmessaging';

    const DEBUG = false;

    public static $now = '';

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

    public static $httpUser = 'test';

    public static $httpPassword = 'test';

    public static $http_curl_config = array();

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
     * Logging PSR3 compatible
     */
    public static $log = null;

    /*
     * -----------------------------------------------------------------------
     * Mail settings
     */
    public static $mails_per_minute = 300;

    /*
     * -----------------------------------------------------------------------
     * SMTP settings
     */
    public static $smtp_enabled = false;

    public static $smtp_host = null;

    public static $smtp_port = null;

    public static $smtp_auth_enabled = true;

    public static $smtp_auth_method = null;

    public static $smtp_username = null;

    public static $smtp_password = null;

    public static $smtp_skip_tls_verify = false;

    public static $verify_dns_enabled = false;

    public static $smtp_debug = false;
}

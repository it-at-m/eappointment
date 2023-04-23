<?php
/**
 *
 * @package Zmsmessaging
 *
 */
namespace BO\Zmsmessaging;

class Application
{

    /**
     * Name of the application
     */
    const IDENTIFIER = 'Zmsmessaging';

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
    const CONFIG_SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';


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
}

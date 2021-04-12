<?php
/**
 *
 * @package Zmsmessaging
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
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

    public static $httpUser = '_system_messenger';

    public static $httpPassword = 'zmsmessaging';

    public static $http_curl_config = array();

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

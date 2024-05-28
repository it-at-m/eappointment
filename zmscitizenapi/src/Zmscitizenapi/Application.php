<?php

namespace BO\Zmscitizenapi;

use BO\Zmsclient\Http;

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'Zmscitizenapi';

    /**
     * HTTP url for api
     */
    const ZMS_API_URL = 'http://user:pass@host.tdl';

    /**
     * Flag for enabling maintenance mode
     */
    const MAINTENANCE_MODE_ENABLED = false;

    /**
     * Name of the source which should be used for the API
     */
    public static string $source_name = "dldb";

    /**
     * -----------------------------------------------------------------------
     * ZMS API access
     * @var Http $http
     */
    public static $http = null;

    public static $http_curl_config = [];
}

<?php
define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');
define('MAINTENANCE_MODE_ENABLED', filter_var(getenv('MAINTENANCE_ENABLED'), FILTER_VALIDATE_BOOLEAN));
define('ZMS_IDENTIFIER', getenv('ZMS_IDENTIFIER') ? getenv('ZMS_IDENTIFIER') : 'zms');
define('ZMS_MODULE_NAME', 'zmscitizenapi');
define('ZMS_API_PASSWORD_CITIZENAPI', getenv('ZMS_API_PASSWORD_CITIZENAPI'));

class App extends \BO\Zmscitizenapi\Application
{
    /**
     * HTTP url for api
     */
    const ZMS_API_URL = ZMS_API_URL;

    /**
     * Flag for enabling maintenance mode
     */
    const MAINTENANCE_MODE_ENABLED = MAINTENANCE_MODE_ENABLED;

    /**
     * Name of the application
     */
    const IDENTIFIER = ZMS_IDENTIFIER;

    /**
     * Name of the module
     */
    const MODULE_NAME = ZMS_MODULE_NAME;

    /**
     * User for the upstream API
     */
    public static $httpUser = '_system_citizenapi';

    /**
     * Password for the upstream API
     */
    public static $httpPassword = ZMS_API_PASSWORD_CITIZENAPI;

}
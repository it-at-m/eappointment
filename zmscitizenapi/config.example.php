<?php

define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');
define('MAINTENANCE_MODE_ENABLED', in_array(strtolower(getenv('ZMS_API_URL')), ["1", "true", "yes"]));

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
}
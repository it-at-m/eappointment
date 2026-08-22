<?php

define('ZMS_IDENTIFIER', getenv('ZMS_IDENTIFIER') ? getenv('ZMS_IDENTIFIER') : 'zms');
define('ZMS_MODULE_NAME', 'zmscitizenbackend');

class App extends \BO\Zmscitizenbackend\Application
{
    /**
     * Name of the application
     */
    public const string IDENTIFIER = ZMS_IDENTIFIER;

    /**
     * Name of the module
     */
    public const string MODULE_NAME = ZMS_MODULE_NAME;
}

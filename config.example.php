<?php
// @codingStandardsIgnoreFile
 
define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');

class App extends \BO\Zmsadmin\Application
{
    const IDENTIFIER = 'Zmsadmin-ENV';
    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = ZMS_API_URL;

}

<?php
// @codingStandardsIgnoreFile
 
define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');
define('AUTHORIZATION_TYPE', getenv('AUTHORIZATION_TYPE') ? getenv('AUTHORIZATION_TYPE') : 'https://localhost/terminvereinbarung/api/2');
define('AUTHORIZATION_PROVIDER_URL', getenv('AUTHORIZATION_PROVIDER_URL') ? getenv('AUTHORIZATION_PROVIDER_URL') : 'https://localhost/terminvereinbarung/auth/realms/zms/');
define('AUTHORIZATION_CLIENT_ID', getenv('AUTHORIZATION_CLIENT_ID') ? getenv('AUTHORIZATION_CLIENT_ID') : 'zmsadmin');
define('AUTHORIZATION_CLIENT_SECRET', getenv('AUTHORIZATION_CLIENT_SECRET') ? getenv('AUTHORIZATION_CLIENT_SECRET') : null);

class App extends \BO\Zmsadmin\Application
{
    const IDENTIFIER = 'Zmsadmin-ENV';
    const DEBUG = false;
    const AUTHORIZATION_TYPE = AUTHORIZATION_TYPE;
    const AUTHORIZATION_PROVIDER_URL = AUTHORIZATION_PROVIDER_URL;
    const AUTHORIZATION_CLIENT_ID = AUTHORIZATION_CLIENT_ID;
    const AUTHORIZATION_CLIENT_SECRET = AUTHORIZATION_CLIENT_SECRET;
    // Per default uses dir ./cache
    //const TWIG_CACHE = false;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = ZMS_API_URL;

    public static $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 25,
        //CURLOPT_VERBOSE => true,
    ];
}

if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}

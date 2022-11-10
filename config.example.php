<?php
// @codingStandardsIgnoreFile
 
define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');
define('OIDC_AUTHORIZATION_TYPE', getenv('OIDC_AUTHORIZATION_TYPE') ? getenv('OIDC_AUTHORIZATION_TYPE') : 'local');

class App extends \BO\Zmsadmin\Application
{
    const IDENTIFIER = 'Zmsadmin-ENV';
    const DEBUG = false;
    // Per default uses dir ./cache
    //const TWIG_CACHE = false;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = ZMS_API_URL;

    /**
     * OpenID Connection Type
     */
    const OIDC_AUTHORIZATION_TYPE = OIDC_AUTHORIZATION_TYPE;

    public static $http_curl_config = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 25,
        //CURLOPT_VERBOSE => true,
    ];
}

if (getenv('ZMS_TIMEADJUST')) {
    App::$now = new DateTimeImmutable(date(getenv('ZMS_TIMEADJUST')), new DateTimeZone('Europe/Berlin'));
}

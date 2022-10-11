<?php
// @codingStandardsIgnoreFile
 
define('ZMS_API_URL', getenv('ZMS_API_URL') ? getenv('ZMS_API_URL') : 'https://localhost/terminvereinbarung/api/2');
define('ZMS_AUTHORIZATION_TYPE', getenv('ZMS_AUTHORIZATION_TYPE') ? getenv('ZMS_AUTHORIZATION_TYPE') : 'local');
define('ZMS_AUTHORIZATION_AUTHSERVERURL', getenv('ZMS_AUTHORIZATION_AUTHSERVERURL') ? getenv('ZMS_AUTHORIZATION_AUTHSERVERURL') : 'https://localhost/terminvereinbarung/auth/realms/zms/');
define('ZMS_AUTHORIZATION_REALM', getenv('ZMS_AUTHORIZATION_REALM') ? getenv('ZMS_AUTHORIZATION_REALM') : 'ZMS');
define('ZMS_AUTHORIZATION_CLIENT_ID', getenv('ZMS_AUTHORIZATION_CLIENT_ID') ? getenv('ZMS_AUTHORIZATION_CLIENT_ID') : 'zmsadmin');
define('ZMS_AUTHORIZATION_CLIENT_SECRET', getenv('ZMS_AUTHORIZATION_CLIENT_SECRET') ? getenv('ZMS_AUTHORIZATION_CLIENT_SECRET') : null);
define('ZMS_AUTHORIZATION_REDIRECTURI', getenv('ZMS_AUTHORIZATION_REDIRECTURI') ? getenv('ZMS_AUTHORIZATION_REDIRECTURI') : null);
define('ZMS_AUTHORIZATION_ACCESS_ROLE', getenv('ZMS_AUTHORIZATION_ACCESS_ROLE') ? getenv('ZMS_AUTHORIZATION_ACCESS_ROLE') : 'Access_ZMS');

class App extends \BO\Zmsadmin\Application
{
    const IDENTIFIER = 'Zmsadmin-ENV';
    const DEBUG = false;
    const ZMS_AUTHORIZATION_TYPE = ZMS_AUTHORIZATION_TYPE;
    const ZMS_AUTHORIZATION_AUTHSERVERURL = ZMS_AUTHORIZATION_AUTHSERVERURL;
    const ZMS_AUTHORIZATION_REALM = ZMS_AUTHORIZATION_REALM;
    const ZMS_AUTHORIZATION_CLIENT_ID = ZMS_AUTHORIZATION_CLIENT_ID;
    const ZMS_AUTHORIZATION_CLIENT_SECRET = ZMS_AUTHORIZATION_CLIENT_SECRET;
    const ZMS_AUTHORIZATION_REDIRECTURI = ZMS_AUTHORIZATION_REDIRECTURI;
    const ZMS_AUTHORIZATION_ACCESS_ROLE = ZMS_AUTHORIZATION_ACCESS_ROLE;
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

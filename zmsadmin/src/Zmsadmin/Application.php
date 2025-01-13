<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsclient\Http;

define(
    'ZMS_ADMIN_TEMPLATE_FOLDER',
    getenv('ZMS_ADMIN_TEMPLATE_FOLDER') ? getenv('ZMS_ADMIN_TEMPLATE_FOLDER') : '/templates/'
);

define(
    'ZMS_ADMIN_SESSION_DURATION',
    getenv('ZMS_ADMIN_SESSION_DURATION') ? getenv('ZMS_ADMIN_SESSION_DURATION') : 28800
);

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     *
     */
    const IDENTIFIER = 'Zmsadmin';

    const DEBUG = false;

    const TWIG_CACHE = '/cache/';

    const TEMPLATE_PATH = ZMS_ADMIN_TEMPLATE_FOLDER;

    const SESSION_DURATION = ZMS_ADMIN_SESSION_DURATION;

    public static $includeUrl = '/terminvereinbarung/admin';

    /**
     * allow cluster wide process calls
     */

    public static $allowClusterWideCall = true;

    /**
     * image preferences
     */

    public static $isImageAllowed = false;

    /**
     * language preferences
     */
    const MULTILANGUAGE = true;

    public static $locale = 'de';
    public static $supportedLanguages = array(
         // Default language
         'de' => array(
             'name'    => 'Deutsch',
             'locale'  => 'de_DE',
             'default' => true,
         ),
         // Other languages
         'en' => array(
             'name'    => 'English',
             'locale'  => 'en_GB',
         )
     );

    /**
    * config preferences
    */
    const CONFIG_SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

    /**
     * signature key for url signature to save query paramter with hash
     */
    public static $urlSignatureSecret = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

    /**
     * -----------------------------------------------------------------------
     * ZMS API access
     * @var Http $http
     */
    public static $http = null;

    public static $http_curl_config = array();

    const CLIENTKEY = '';

    const JSON_COMPRESS_LEVEL = 1;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = 'http://user:pass@host.tdl';
}

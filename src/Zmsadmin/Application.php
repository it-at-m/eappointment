<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     *
     */
    const IDENTIFIER = 'Zmsadmin';

    const DEBUG = false;

    const TWIG_CACHE = '/cache/';

    public static $includeUrl = '/terminvereinbarung/admin';
    /**
     * language preferences
     */
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

    /*
     * -----------------------------------------------------------------------
     * ZMS API access
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

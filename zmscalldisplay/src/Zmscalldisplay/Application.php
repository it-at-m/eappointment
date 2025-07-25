<?php

/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmscalldisplay;

if (!getenv('ZMS_CONFIG_SECURE_TOKEN')) {
    throw new \RuntimeException('ZMS_CONFIG_SECURE_TOKEN environment variable must be set');
}

define('ZMS_CONFIG_SECURE_TOKEN', getenv('ZMS_CONFIG_SECURE_TOKEN'));

if (!defined('ZMS_CALLDISPLAY_TWIG_CACHE')) {
    $value = getenv('ZMS_CALLDISPLAY_TWIG_CACHE');
    define('ZMS_CALLDISPLAY_TWIG_CACHE', ($value === 'false') ? false : ($value ?: '/cache/'));
}

class Application extends \BO\Slim\Application
{
    /**
     * Name of the application
     */
    const IDENTIFIER = 'zms';
    const MODULE_NAME = 'zmscalldisplay';
    const DEBUG = false;
    const TWIG_CACHE = ZMS_CALLDISPLAY_TWIG_CACHE;

    /**
     * language preferences
     */
    public static $locale = 'de';

    public static $supportedLanguages = array(
        // Default language
        'de' => array(
            'name'    => 'Deutsch',
            'locale'  => 'de_DE.utf-8',
            'default' => true,
        ),
        'en' => array(
            'name'    => 'English',
            'locale'  => 'en_GB.utf-8',
            'default' => false,
        )
    );

    public static $now = '';

    /*
     * -----------------------------------------------------------------------
     * ZMS API access
     */

    public static $http = null;

    public static $http_curl_config = array();

    const JSON_COMPRESS_LEVEL = 1;

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = 'http://user:pass@host.tdl';
    const SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';

     /**
     * signature key for url signature to save query paramter with hash
     */
    public static $urlSignatureSecret = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';
}

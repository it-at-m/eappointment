<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

class Application extends \BO\Slim\Application
{

    /**
     * Name of the application
     */
    const IDENTIFIER = 'Zmsticketprinter';

    const DEBUG = false;

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

    /**
     * HTTP url for api
     */
    const HTTP_BASE_URL = 'http://user:pass@host.tdl';
    const SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';
}

<?php
/**
 * @package Slimproject
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

class Bootstrap
{
    public static function init()
    {       
    	self::configureSlim();
    	self::configureLocale();
    	self::configureLogger();              
    }

    public static function configureLocale(
        $charset = \App::CHARSET,
        $timezone = \App::TIMEZONE
    ) {
        ini_set('default_charset', $charset);
        date_default_timezone_set($timezone);
        mb_internal_encoding($charset);
        
        $language = \App::$locale[self::getLanguage()];
        putenv('LC_ALL='. $language);
        $locale = setlocale(LC_ALL, $language);
        
        // Specify the location of the translation tables
        bindtextdomain('dldb-'.$language, \App::APP_PATH. '/locale');
        bind_textdomain_codeset('dldb-'.$language, 'UTF-8');
        
        // Choose domain
        textdomain('dldb-'.$language);       
    }

    public static function configureLogger(
        $level = \App::MONOLOG_LOGLEVEL,
        $identifier = \App::IDENTIFIER
    ) {
        \App::$log = new \Monolog\Logger($identifier);
        \App::$log->pushHandler(new \Monolog\Handler\ErrorLogHandler(
            \Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM,
            $level
        ));
    }

    public static function configureSlim()
    {
        // configure slim
        \App::$slim = new i18nSlim(array(
            'debug' => \App::SLIM_DEBUG,
            'log.enabled' => \App::SLIM_DEBUG,
            'log.level' => \App::SLIM_LOGLEVEL,
            'view' => new \Slim\Views\Twig(),
            'templates.path' => \App::APP_PATH  . \App::TEMPLATE_PATH
        ));

        // configure slim views with twig
        \App::$slim->view()->parserOptions = array (
            'debug' => \App::SLIM_DEBUG,
            'cache' => \App::TWIG_CACHE ? \App::APP_PATH . \App::TWIG_CACHE : false,
        );
        self::addTwigExtension(new \Slim\Views\TwigExtension());
        self::addTwigExtension(new \BO\Slim\TwigExtension());
        self::addTwigExtension(new \Twig_Extension_Debug());

        //self::addTwigTemplateDirectory('default', \App::APP_PATH . \App::TEMPLATE_PATH);
    }
    
	public static function getLanguage()
    {
    	\Slim\Route::setDefaultConditions(array(
    			'lang'=> implode('|', array_keys(\App::$locale))
    	));    	
    	$lang = substr(\App::$slim->request()->getResourceUri(), 1, 2);
    	$lang = in_array($lang, array_keys(\App::$locale))? $lang : \App::DEFAULT_LANG;
    	$lang = ($lang == '') ? $lang = 'de' : $lang;
    	\App::$slim->config('lang', $lang);
    	return $lang;
    }

    public static function addTwigExtension($extension)
    {
        $twig = \App::$slim->view->getInstance();
        $twig->addExtension($extension);
    }
    
    public static function addTwigFilter($filter)
    {
    	$twig = \App::$slim->view->getInstance();
    	$twig->addFilter($filter);
    }

    public static function addTwigTemplateDirectory($namespace, $path)
    {
        $twig = \App::$slim->view->getInstance();
        $loader = $twig->getLoader();
        $loader->addPath($path, $namespace);
    }
}

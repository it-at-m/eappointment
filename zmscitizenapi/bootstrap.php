<?php

use BO\Zmscitizenapi\Services\Core\LoggerService;
// @codingStandardsIgnoreFile
chdir(__DIR__);

// define the application path as single global constant
if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__));
}

// use autoloading offered by composer, see composer.json for path settings
if (file_exists(APP_PATH . '/vendor/autoload.php')) {
    define('VENDOR_PATH', APP_PATH . '/vendor');
} else {
    define('VENDOR_PATH', APP_PATH . '/../..');
}
require_once(VENDOR_PATH . '/autoload.php');


// initialize the static \App singleton
require(APP_PATH . '/config.php');

// Set option for environment, routing, logging and templating
\BO\Slim\Bootstrap::init();

\App::$slim->addBodyParsingMiddleware();

\App::$http = new \BO\Zmsclient\Http(\App::ZMS_API_URL);
//\BO\Zmsclient\Psr7\Client::$curlopt = \App::$http_curl_config;

$errorMiddleware = \App::$slim->getContainer()->get('errorMiddleware');
$errorMiddleware->setDefaultErrorHandler(new \BO\Zmscitizenapi\Helper\ErrorHandler());

// Initialize cache for rate limiting
$cache = new \Symfony\Component\Cache\Psr16Cache(
    new \Symfony\Component\Cache\Adapter\FilesystemAdapter()
);


$logger = new LoggerService();
// Security middleware (order is important)
App::$slim->add(new \BO\Zmscitizenapi\Middleware\LanguageMiddleware($logger));
App::$slim->add(new \BO\Zmscitizenapi\Middleware\RequestLoggingMiddleware($logger));
App::$slim->add(new \BO\Zmscitizenapi\Middleware\SecurityHeadersMiddleware($logger));
App::$slim->add(new \BO\Zmscitizenapi\Middleware\CorsMiddleware($logger));
//App::$slim->add(new \BO\Zmscitizenapi\Middleware\CsrfMiddleware($logger));
App::$slim->add(new \BO\Zmscitizenapi\Middleware\RateLimitingMiddleware($cache, $logger));
App::$slim->add(new \BO\Zmscitizenapi\Middleware\RequestSanitizerMiddleware($logger));
App::$slim->add(new \BO\Zmscitizenapi\Middleware\RequestSizeLimitMiddleware($logger));
App::$slim->add(new \BO\Zmscitizenapi\Middleware\IpFilterMiddleware($logger));

// Add handler for Method Not Allowed
$errorMiddleware->setErrorHandler(
    \Slim\Exception\HttpMethodNotAllowedException::class,
    function (
        \Psr\Http\Message\ServerRequestInterface $request,
        \Throwable $exception
    ) {
        $response = \App::$slim->getResponseFactory()->createResponse();
        $response = $response->withStatus(405)
            ->withHeader('Content-Type', 'application/json');

        $currentLanguage = $request->getAttribute('language');
        $responseBody = json_encode([
            'errors' => [
                \BO\Zmscitizenapi\Localization\ErrorMessages::get('requestMethodNotAllowed', $currentLanguage)
            ]
        ]);

        error_log($responseBody);
        
        $response->getBody()->write($responseBody);
        return $response;
    }
);

// load routing
\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');

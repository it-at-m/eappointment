<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use \Slim\Http\Headers;
use \Slim\Http\Request;
use \Slim\Http\Response;

class Render
{

    /**
     * @var \Interop\Container\ContainerInterface $containerInterface
     *
     */
    public static $container = null;

    /**
     * @var \Psr\Http\Message\RequestInterface $request;
     *
     */
    public static $request = null;

    /**
     * @var \Psr\Http\Message\ResponseInterface $response;
     *
     */
    public static $response = null;

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function error404()
    {
        \App::$slim->notFound();
        return self::$response;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function html($template, $parameters = array(), $status = 200)
    {
        self::$response = self::$response->withStatus($status);
        self::$response = self::$response->withHeader('Content-Type', 'text/html; charset=utf-8');
        self::$response = self::$container->view->render(self::$response, $template, $parameters);
        return self::$response;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function json($data, $status = 200)
    {
        self::$response = self::$response->withStatus($status);
        self::$response = self::$response->withHeader('Content-Type', 'application/json');
        self::$response->getBody()->write(json_encode($data));
        return self::$response;
    }

    /**
     * @param String $date strtotime interpreted
     * @param String $expires strtotime interpreted
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function lastModified($date, $expires = '+5 minutes')
    {
        self::$response = self::getCachableResponse(self::$response, $date, $expires);
        return self::$response;
    }

    /**
     * @param String $date strtotime interpreted
     * @param String $expires strtotime interpreted
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function getCachableResponse(
        \Psr\Http\Message\ResponseInterface $response,
        $date,
        $expires = '+5 minutes'
    ) {
        if (!is_int($date)) {
            $date = strtotime($date);
        }
        if (false === strtotime($expires)) {
            $expires = '+' + $expires + ' seconds';
        }
        $response = \App::$slim->getContainer()->cache->withExpires($response, $expires);
        $response = \App::$slim->getContainer()->cache->withLastModified($response, $date);
        return $response;
    }

    /**
     * @param String $route_name
     * @param Array $arguments parameters in the route path
     * @param Array $parameter parameters to append with "?"
     * @param Int $statuscode see an HTTP reference
     *
     * @return NULL
     */
    public static function redirect($route_name, $arguments, $parameter, $statuscode = 302)
    {
        $response = new Response($statuscode);
        $url = \App::$slim->urlFor($route_name, $arguments);
        $url = Helper::proxySanitizeUri($url);
        $url = preg_replace('#^.*?(https?://)#', '\1', $url); // allow http:// routes
        if ($parameter) {
            $url .= '?' . http_build_query($parameter);
        }
        return $response->withHeader('Location', $url);
    }
}
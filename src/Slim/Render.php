<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use App;
use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Slim\Response;

class Render
{

    /**
     * @var ContainerInterface $containerInterface
     *
     */
    public static $container = null;

    /**
     * @var RequestInterface $request;
     *
     */
    public static $request = null;

    /**
     * @var ResponseInterface $response;
     *
     */
    public static $response = null;

    /**
     * @return ResponseInterface
     */
    public static function withHtml(ResponseInterface $response, $template, $parameters = array(), $status = 200)
    {
        Profiler::add("Controller");
        $response  = $response->withStatus($status);
        $response  = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        App::$templatedefaults['debug'] = App::DEBUG;
        $parameters = array_merge(App::$templatedefaults, $parameters);
        $response  = App::$slim->getContainer()->get('view')->render($response, $template, $parameters);
        Profiler::add("Rendering");
        return $response ;
    }

    /**
     * @return ResponseInterface
     */
    public static function html($template, $parameters = array(), $status = 200)
    {
        self::$response = self::withHtml(self::$response, $template, $parameters, $status);
        return self::$response;
    }

    public static function withXml(ResponseInterface $response, $data, $status = 200)
    {
        Profiler::add("Controller");
        $response = $response->withStatus($status);
        $response = $response->withHeader('Content-Type', 'application/soap+xml');
        $response->getBody()->write($data);
        Profiler::add("Rendering");
        return $response;
    }

    public static function withJson(ResponseInterface $response, $data, $status = 200)
    {
        Profiler::add("Controller");
        $response = $response->withStatus($status);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES));
        Profiler::add("Rendering");
        return $response;
    }

    /**
     * @return ResponseInterface
     */
    public static function json($data, $status = 200)
    {
        self::$response = self::withJson(self::$response, $data, $status);
        return self::$response;
    }

    /**
     * @return ResponseInterface
     */
    public static function xml($data, $status = 200)
    {
        self::$response = self::withXml(self::$response, $data, $status);
        return self::$response;
    }

    /**
     * Add `Last-Modified` header to PSR7 response object
     *
     * @param  ResponseInterface $response A PSR7 response object
     * @param  int|string        $time     A UNIX timestamp or a valid `strtotime()` string
     *
     * @return ResponseInterface           A new PSR7 response object with `Last-Modified` header
     * @throws InvalidArgumentException if the last modified date cannot be parsed
     */
    public static function withLastModified(ResponseInterface $response, $date, $expires = '+5 minutes')
    {
        return self::getCachableResponse($response, $date, $expires);
    }

    /**
     * @param String $date strtotime interpreted
     * @param String $expires strtotime interpreted
     *
     * @return ResponseInterface
     */
    public static function lastModified($date, $expires = '+5 minutes')
    {
        self::$response = self::withLastModified(self::$response, $date, $expires);
        return self::$response;
    }

    /**
     * @param String $date strtotime interpreted
     * @param String $expires strtotime interpreted
     *
     * @return ResponseInterface
     */
    public static function getCachableResponse(
        ResponseInterface $response,
        $date,
        $expires = '+5 minutes'
    ) {

        if (!$date) {
            $date = time();
        } elseif (!is_int($date)) {
            $date = strtotime($date);
        }

        $maxAge = strtotime($expires) - time();
        if (false === strtotime($expires)) {
            $expires = '+'. $expires .' seconds';
            $maxAge = intval($expires);
        }
        $response = $response->withAddedHeader('Cache-Control', 'max-age=' . $maxAge);
        $response = App::$slim->getContainer()->get('cache')->withExpires($response, $expires);
        $response = App::$slim->getContainer()->get('cache')->withLastModified($response, $date);

        return $response;
    }

    /**
     * @param String $route_name
     * @param array $arguments parameters in the route path
     * @param array $parameter parameters to append with "?"
     * @param Int $statuscode see an HTTP reference
     *
     * \Psr\Http\Message\ResponseInterface
     */
    public static function redirect($route_name, $arguments, $parameter = null, $statuscode = 302)
    {
        Profiler::add("Controller");

        $url = App::$slim->urlFor($route_name, $arguments);
        $url = Helper::proxySanitizeUri($url);
        $url = preg_replace('#^.*?(https?://)#', '\1', $url); // allow http:// routes
        if ($parameter) {
            $url .= '?' . http_build_query($parameter);
        }

        $response = App::$slim->getResponseFactory()->createResponse($statuscode);
        $response = App::$slim->getContainer()->get('cache')->denyCache($response);
        /** @var Response $response */
        $response = $response->withHeader('Location', (string) $url);

        return $response->withAddedHeader('Cache-Control', 'max-age=0');
    }
}

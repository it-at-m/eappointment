<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Slim\Response;

class Render
{
    public static ?ContainerInterface $container = null;

    public static ?RequestInterface $request = null;

    public static ?ResponseInterface $response = null;

    public static function withHtml(
        ResponseInterface $response,
        string $template,
        array $parameters = [],
        int $status = 200
    ): ResponseInterface {
        Profiler::add("Controller");
        $response  = $response->withStatus($status);
        $response  = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        App::$templatedefaults['debug'] = App::DEBUG;
        $request = self::$request;
        if (null === $request && null !== self::$container) {
            $request = self::$container->get('request');
            $request = $request instanceof RequestInterface ? $request : null;
        }
        $templateRequest = $request instanceof Request ? $request : null;
        App::$templatedefaults['includeUrl'] = Helper\TemplateUrls::resolveIncludeUrl($templateRequest);
        App::$templatedefaults['baseUrl'] = Helper\TemplateUrls::resolveBaseUrl($templateRequest);
        $parameters = array_merge(App::$templatedefaults, $parameters);
        $response  = self::requireContainer()->get('view')->render($response, $template, $parameters);
        Profiler::add("Rendering");
        return $response ;
    }

    public static function html(string $template, array $parameters = [], int $status = 200): ResponseInterface
    {
        self::$response = self::withHtml(self::requireResponse(), $template, $parameters, $status);
        return self::$response;
    }

    public static function withXml(ResponseInterface $response, string $data, int $status = 200): ResponseInterface
    {
        Profiler::add("Controller");
        $response = $response->withStatus($status);
        $response = $response->withHeader('Content-Type', 'application/soap+xml');
        $response->getBody()->write($data);
        Profiler::add("Rendering");
        return $response;
    }

    public static function withJson(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
    {
        Profiler::add("Controller");
        $response = $response->withStatus($status);
        $response = $response->withHeader('Content-Type', 'application/json');
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($payload !== false ? $payload : 'null');
        Profiler::add("Rendering");
        return $response;
    }

    public static function json(mixed $data, int $status = 200): ResponseInterface
    {
        self::$response = self::withJson(self::requireResponse(), $data, $status);
        return self::$response;
    }

    public static function xml(string $data, int $status = 200): ResponseInterface
    {
        self::$response = self::withXml(self::requireResponse(), $data, $status);
        return self::$response;
    }

    /**
     * Add `Last-Modified` header to PSR7 response object
     *
     * @param ResponseInterface $response A PSR7 response object
     * @param int|string        $date     A UNIX timestamp or a valid `strtotime()` string
     * @param string $expires
     *
     * @return ResponseInterface           A new PSR7 response object with `Last-Modified` header
     *
     */
    public static function withLastModified(
        ResponseInterface $response,
        string|int $date,
        string $expires = '+5 minutes'
    ): ResponseInterface {
        return self::getCachableResponse($response, $date, $expires);
    }

    /**
     * @param string|int $date strtotime interpreted
     * @param string $expires strtotime interpreted
     *
     * @return ResponseInterface
     */
    public static function lastModified(string|int $date, string $expires = '+5 minutes'): ResponseInterface
    {
        self::$response = self::withLastModified(self::requireResponse(), $date, $expires);
        return self::$response;
    }

    /**
     * @param string|int $date strtotime interpreted
     * @param string $expires strtotime interpreted
     *
     * @return ResponseInterface
     */
    public static function getCachableResponse(
        ResponseInterface $response,
        string|int $date,
        string $expires = '+5 minutes'
    ): ResponseInterface {
        if (is_int($date)) {
            $timestamp = $date !== 0 ? $date : time();
        } else {
            $parsed = strtotime($date);
            $timestamp = $parsed !== false ? $parsed : time();
        }

        $expireTs = strtotime($expires);
        if ($expireTs === false) {
            $expires = '+' . $expires . ' seconds';
            $maxAge = (int) $expires;
        } else {
            $maxAge = $expireTs - time();
        }
        $response = $response->withAddedHeader('Cache-Control', 'max-age=' . $maxAge);
        $cache = self::requireContainer()->get('cache');
        $response = $cache->withExpires($response, $expires);
        $response = $cache->withLastModified($response, $timestamp);

        return $response;
    }

    /**
     * @param string $route_name
     * @param array $arguments parameters in the route path
     * @param array|null $parameter parameters to append with "?"
     * @param int $statuscode see an HTTP reference
     */
    public static function redirect(
        string $route_name,
        array $arguments,
        ?array $parameter = null,
        int $statuscode = 302
    ): Response {
        Profiler::add("Controller");

        $url = App::$slim->urlFor($route_name, $arguments);
        $sanitized = Helper::proxySanitizeUri($url);
        $url = is_string($sanitized) ? $sanitized : $url;
        $url = preg_replace('#^.*?(https?://)#', '\1', $url) ?? $url; // allow http:// routes
        if ($parameter !== null && $parameter !== []) {
            $url .= '?' . http_build_query($parameter);
        }

        $response = App::$slim->getResponseFactory()->createResponse($statuscode);
        $response = self::requireContainer()->get('cache')->denyCache($response);
        /** @var Response $response */
        $response = $response->withHeader('Location', $url);

        return $response->withAddedHeader('Cache-Control', 'max-age=0');
    }

    private static function requireContainer(): ContainerInterface
    {
        $container = App::$slim->getContainer();
        if (!$container instanceof ContainerInterface) {
            throw new \RuntimeException('Slim container is not initialized');
        }
        return $container;
    }

    private static function requireResponse(): ResponseInterface
    {
        if (!self::$response instanceof ResponseInterface) {
            throw new \RuntimeException('Render response is not initialized');
        }
        return self::$response;
    }
}

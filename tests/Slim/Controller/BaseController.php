<?php
/**
 * @package zmsslim
 *
 */
namespace BO\Slim\Tests\Controller;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

abstract class BaseController extends \BO\Slim\Controller
{
    public static $sessionAttribute = \App::SESSION_ATTRIBUTE;

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }
}

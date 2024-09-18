<?php

namespace BO\Zmscitizenapi;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

abstract class BaseController extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * @codeCoverageIgnore
     *
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }

    protected function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        $response = $response->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content));
        return $response;
    }
    

}
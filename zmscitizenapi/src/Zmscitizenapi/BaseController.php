<?php

namespace BO\Zmscitizenapi;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

abstract class BaseController extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * Hook method for handling responses in child controllers.
     * Child classes should override this method to implement their specific response logic.
     *
     * @param RequestInterface $request The HTTP request
     * @param ResponseInterface $response The HTTP response
     * @param array $args Route parameters
     * @return ResponseInterface The modified response
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return parent::__invoke($request, $response, $args);
    }

    protected function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new \InvalidArgumentException('Invalid HTTP status code');
        }
    
        $response = $response->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    
        try {
            // Add JSON_UNESCAPED_SLASHES to ensure slashes in HTML are not escaped
            $json = json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to encode JSON response: ' . $e->getMessage(), 0, $e);
        }
    
        $response->getBody()->write($json);
    
        return $response;
    }
    
}
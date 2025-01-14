<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceMiddleware implements MiddlewareInterface
{
    private const HTTP_UNAVAILABLE = 503;
    private const ERROR_UNAVAILABLE = 'serviceUnavailable';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (\App::MAINTENANCE_MODE_ENABLED) {
            $language = $request->getAttribute('language');
            
            $error = ErrorMessages::get(self::ERROR_UNAVAILABLE, $language);
            
            $response = \App::$slim->getResponseFactory()->createResponse();
            $response = $response->withStatus(self::HTTP_UNAVAILABLE)
                ->withHeader('Content-Type', 'application/json');

            // Write JSON response
            $responseBody = json_encode([
                'errors' => [$error]
            ]);
            $response->getBody()->write($responseBody);

            return $response;
        }

        return $handler->handle($request);
    }
}
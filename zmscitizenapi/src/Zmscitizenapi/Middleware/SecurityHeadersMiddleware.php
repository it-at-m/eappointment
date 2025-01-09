<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    private const ERROR_SECURITY_VIOLATION = 'securityHeaderViolation';
    private array $securityHeaders = [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self'",
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'X-Permitted-Cross-Domain-Policies' => 'none'
    ];

    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $response = $handler->handle($request);
            
            foreach ($this->securityHeaders as $header => $value) {
                $response = $response->withHeader($header, $value);
            }
            
            $this->logger->logInfo('Security headers added', [
                'uri' => (string)$request->getUri()
            ]);
            
            return $response;
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            
            $response = \App::$slim->getResponseFactory()->createResponse();
            $response = $response->withStatus(ErrorMessages::get('securityHeaderViolation')['statusCode'])
                ->withHeader('Content-Type', 'application/json');
            
            $response->getBody()->write(json_encode([
                'errors' => [ErrorMessages::get('securityHeaderViolation')]
            ]));
            
            return $response;
        }
    }
}
<?php

declare(strict_types=1);

namespace BO\Slim\Middleware;

use BO\Slim\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    private const DEFAULT_SECURITY_HEADERS = [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'",
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'X-Permitted-Cross-Domain-Policies' => 'none',
    ];

    /** @var array<string, string> */
    private array $securityHeaders;

    private LoggerService $logger;

    /** @var callable(\Throwable, ServerRequestInterface): ResponseInterface|null */
    private $errorResponseBuilder;

    /**
     * @param array<string, string>|null $securityHeaders
     * @param callable(\Throwable, ServerRequestInterface): ResponseInterface|null $errorResponseBuilder
     */
    public function __construct(
        LoggerService $logger,
        ?array $securityHeaders = null,
        $errorResponseBuilder = null
    ) {
        $this->logger = $logger;
        $this->securityHeaders = $securityHeaders ?? self::DEFAULT_SECURITY_HEADERS;
        $this->errorResponseBuilder = $errorResponseBuilder;
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
            foreach ($this->securityHeaders as $header => $value) {
                $response = $response->withHeader($header, $value);
            }

            return $response;
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            if ($this->errorResponseBuilder !== null) {
                $response = ($this->errorResponseBuilder)($e, $request);
                if ($response !== null) {
                    return $response;
                }
            }
            throw $e;
        }
    }
}

<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    private const ERROR_CORS = 'corsOriginNotAllowed';
    private array $whitelist = [];
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
        $corsEnv = getenv('CORS');
        if ($corsEnv) {
            $this->whitelist = array_map('trim', explode(',', $corsEnv));
        }
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $origin = $request->getHeaderLine('Origin');
            
            // Allow requests without Origin header (direct browser access)
            if (empty($origin)) {
                $this->logger->logInfo('Direct browser request - no Origin header', [
                    'uri' => (string)$request->getUri(),
                    'headers' => $request->getHeaders()
                ]);
                return $handler->handle($request);
            }
            
            if (!$this->isOriginAllowed($origin)) {
                $this->logger->logInfo(sprintf(
                    'CORS blocked - Origin %s not allowed. URI: %s',
                    $origin,
                    $request->getUri()
                ));
                
                $response = \App::$slim->getResponseFactory()->createResponse();
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_CORS)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_CORS)]
                ]));
                
                return $response;
            }
    
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-CSRF-Token')
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Max-Age', '86400');
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }

    private function isOriginAllowed(string $origin): bool
    {
        return in_array($origin, $this->whitelist, true);
    }
}
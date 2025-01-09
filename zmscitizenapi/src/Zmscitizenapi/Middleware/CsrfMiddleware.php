<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    private const ERROR_TOKEN_MISSING = 'csrfTokenMissing';
    private const ERROR_TOKEN_INVALID = 'csrfTokenInvalid';
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];
    private const TOKEN_LENGTH = 32;
    private const SESSION_TOKEN_KEY = 'csrf_token';

    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            if (in_array($request->getMethod(), self::SAFE_METHODS, true)) {
                $this->ensureTokenExists();
                return $handler->handle($request);
            }

            $token = $request->getHeaderLine('X-CSRF-Token');
            if (empty($token)) {
                $this->logger->logInfo('CSRF token missing', [
                    'uri' => (string)$request->getUri()
                ]);
                
                $response = \App::$slim->getResponseFactory()->createResponse();
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_TOKEN_MISSING)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_TOKEN_MISSING)]
                ]));
                
                return $response;
            }

            if (!$this->validateToken($token)) {
                $this->logger->logInfo('Invalid CSRF token', [
                    'uri' => (string)$request->getUri()
                ]);
                
                $response = \App::$slim->getResponseFactory()->createResponse();
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_TOKEN_INVALID)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_TOKEN_INVALID)]
                ]));
                
                return $response;
            }

            return $handler->handle($request);
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }

    private function validateToken(string $token): bool
    {
        if (strlen($token) !== self::TOKEN_LENGTH || !ctype_xdigit($token)) {
            return false;
        }
        
        $storedToken = $this->getStoredToken();
        if (empty($storedToken)) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    private function ensureTokenExists(): void
    {
        if (empty($this->getStoredToken())) {
            $this->generateNewToken();
        }
    }

    private function generateNewToken(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH / 2));
        $_SESSION[self::SESSION_TOKEN_KEY] = $token;
        return $token;
    }

    private function getStoredToken(): string
    {
        return $_SESSION[self::SESSION_TOKEN_KEY] ?? '';
    }

    public function getToken(): string
    {
        $this->ensureTokenExists();
        return $this->getStoredToken();
    }
}
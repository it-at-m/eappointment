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
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS']; //Remove POST DELETE and PUT when in use
    
    private int $tokenLength;
    private string $sessionKey;
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
        $config = \App::getCsrfConfig();
        $this->tokenLength = $config['tokenLength'];
        $this->sessionKey = $config['sessionKey'];
        
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
                $language = $request->getAttribute('language');
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_TOKEN_MISSING, $language)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_TOKEN_MISSING, $language)]
                ]));
                
                return $response;
            }

            if (!$this->validateToken($token)) {
                $this->logger->logInfo('Invalid CSRF token', [
                    'uri' => (string)$request->getUri()
                ]);
                
                $response = \App::$slim->getResponseFactory()->createResponse();
                $language = $request->getAttribute('language');
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_TOKEN_INVALID, $language)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_TOKEN_INVALID, $language)]
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
        if (strlen($token) !== $this->tokenLength || !ctype_xdigit($token)) {
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
        $token = bin2hex(random_bytes($this->tokenLength / 2));
        $_SESSION[$this->sessionKey] = $token;
        return $token;
    }

    private function getStoredToken(): string
    {
        return $_SESSION[$this->sessionKey] ?? '';
    }

    public function getToken(): string
    {
        $this->ensureTokenExists();
        return $this->getStoredToken();
    }
}
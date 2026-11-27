<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Utils\ClientIpHelper;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Utils\IpList;
use BO\Slim\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IpFilterMiddleware implements MiddlewareInterface
{
    private const string ERROR_BLACKLISTED = 'ipBlacklisted';
    private string $blacklist;
    private LoggerService $logger;
    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
        $this->blacklist = \App::getIpBlacklist();
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $ip = ClientIpHelper::getClientIp();
            $uri = (string)$request->getUri();
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $this->logger->logInfo('Invalid IP address detected', [
                    'ip' => $ip,
                    'uri' => $uri
                ]);
                return $handler->handle($request);
            }

            if (IpList::contains($ip, $this->blacklist)) {
                return $this->deny(self::ERROR_BLACKLISTED, 'Access denied - IP blacklisted', $ip, $uri);
            }

            return $handler->handle($request);
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }

    private function deny(
        string $errorKey,
        string $logMessage,
        string $ip,
        string $uri
    ): ResponseInterface {
        $this->logger->logInfo($logMessage, [
            'ip' => $ip,
            'uri' => $uri
        ]);
        $error = ErrorMessages::get($errorKey);
        $response = \App::$slim->getResponseFactory()->createResponse();
        $response = $response->withStatus($error['statusCode'])
            ->withHeader('Content-Type', 'application/json');
        $responseBody = json_encode([
            'errors' => [$error]
        ]);
        $response->getBody()->write($responseBody);
        return $response;
    }
}

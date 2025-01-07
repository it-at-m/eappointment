<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Helper;

use BO\Zmscitizenapi\Services\Core\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorHandlerInterface;

class ErrorHandler implements ErrorHandlerInterface
{
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $statusCode = $this->getStatusCode($exception);
        
        if ($logErrors) {
            $this->logError($exception, $request, $displayErrorDetails, $logErrorDetails);
        }

        $response = new \Slim\Psr7\Response();
        $payload = $this->formatErrorPayload($exception, $displayErrorDetails);
        
        $response->getBody()->write(json_encode($payload));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    private function getStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getCode();
        }

        return 500;
    }

    private function formatErrorPayload(\Throwable $exception, bool $displayErrorDetails): array
    {
        $error = [
            'message' => $this->getErrorMessage($exception, $displayErrorDetails),
            'code' => $exception->getCode()
        ];

        if ($displayErrorDetails) {
            $error['type'] = get_class($exception);
            $error['file'] = $exception->getFile();
            $error['line'] = $exception->getLine();
            $error['trace'] = $exception->getTrace();
        }

        return ['error' => $error];
    }

    private function getErrorMessage(\Throwable $exception, bool $displayErrorDetails): string
    {
        if ($displayErrorDetails) {
            return $exception->getMessage();
        }

        if ($exception instanceof HttpException) {
            return $exception->getMessage();
        }

        return 'An internal error has occurred.';
    }

    private function logError(
        \Throwable $exception,
        ServerRequestInterface $request,
        bool $displayErrorDetails,
        bool $logErrorDetails
    ): void {
        LoggerService::logError($exception, $request, null, [
            'displayErrorDetails' => $displayErrorDetails,
            'logErrorDetails' => $logErrorDetails,
            'uri' => (string)$request->getUri(),
            'method' => $request->getMethod(),
            'ip' => ClientIpHelper::getClientIp()
        ]);
    }
}
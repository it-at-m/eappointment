<?php

namespace BO\Zmsbackend\Helper;

use BO\Slim\Render;
use BO\Slim\Helper\Sanitizer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     * @SuppressWarnings(Complexity)
     */
    #[\Override]
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $response = \App::$slim->getResponseFactory()->createResponse();
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            $message = \BO\Zmsbackend\Api\Response\Message::create($request);
            $message->meta['error'] = true;
            $message->meta['message'] = "Could not find a resource with the given URL " . $request->getUri()->getPath();
            $response = \BO\Slim\Render::withLastModified($response, time(), '0');
            return Render::withJson($response, $message, 404);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->meta['error'] = true;
        $message->meta['message'] = $exception->getMessage();
        $message->meta['exception'] = get_class($exception);
        $trace = '';
        foreach (array_slice($exception->getTrace(), 0, 10) as $call) {
            $trace .= "\\";
            $trace .= $call['class'] ?? '';
            $trace .= "::";
            $trace .= $call['function'] ?? '';
            $trace .= " +";
            $trace .= isset($call['line']) ? (string) $call['line'] : '';
            $trace .= "\n";
        }
        $trace = Sanitizer::sanitizeStackTrace($trace);
        $message->meta['trace'] = $trace;

        $exceptionVars = get_object_vars($exception);
        if (array_key_exists('data', $exceptionVars)) {
            $message->data = $exceptionVars['data'];
        }
        $response = \BO\Slim\Render::withLastModified($response, time(), '0');
        $code = (int) $exception->getCode();
        $status = 500;
        if ($code >= 200 && $code <= 599) {
            $status = $code;
        }
        if ($code >= 500 || $code === 0) {
            $collapsedTrace = preg_replace("#(\s)+#", ' ', str_replace('\\', ':', $trace));
            \App::$log->critical(
                "[API] Fatal Exception: "
                . " in " . $exception->getFile() . " +" . $exception->getLine()
                . " -> " . $exception->getMessage()
                . " | Trace: " . Sanitizer::sanitizeStackTrace(
                    is_string($collapsedTrace) ? $collapsedTrace : $trace
                )
            );
        }
        return Render::withJson($response, $message, $status);
    }
}

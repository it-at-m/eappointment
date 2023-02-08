<?php

namespace BO\Zmsapi\Helper;

use \BO\Slim\Render;
use \Psr\Http\Message\ResponseInterface;
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
     * @param ServerRequestInterface $request
     * @param \Throwable $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $response = \App::$slim->getResponseFactory()->createResponse();
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            $message = \BO\Zmsapi\Response\Message::create($request);
            $message->meta->error = true;
            $message->meta->message = "Could not find a resource with the given URL " . $request->getUri()->getPath();
            $response = \BO\Slim\Render::withLastModified($response, time(), '0');
            return Render::withJson($response, $message, 404);
        }

        $message = \BO\Zmsapi\Response\Message::create($request);
        $message->meta->error = true;
        $message->meta->message = $exception->getMessage();
        $message->meta->exception = get_class($exception);
        $message->meta->trace = '';
        foreach (array_slice($exception->getTrace(), 0, 10) as $call) {
            $message->meta->trace .= "\\";
            $message->meta->trace .= isset($call['class']) ? $call['class'] : '';
            $message->meta->trace .= "::";
            $message->meta->trace .= isset($call['function']) ? $call['function'] : '';
            $message->meta->trace .= " +";
            $message->meta->trace .= isset($call['line']) ? $call['line'] : '';
            $message->meta->trace .= "\n";
        }
        if (isset($exception->data)) {
            $message->data = $exception->data;
        }
        $response = \BO\Slim\Render::withLastModified($response, time(), '0');
        $status = 500;
        if ($exception->getCode() >= 200 && $exception->getCode() <= 599) {
            $status = $exception->getcode();
        }
        if ($exception->getCode() >= 500 || !$exception->getCode()) {
            \App::$log->critical(
                "[API] Fatal Exception: "
                . " in " . $exception->getFile() . " +" . $exception->getLine()
                . " -> " . $exception->getMessage()
                . " | Trace: " . preg_replace("#(\s)+#", ' ', str_replace('\\', ':', $message->meta->trace))
            );
        }
        return Render::withJson($response, $message, $status);
    }
}

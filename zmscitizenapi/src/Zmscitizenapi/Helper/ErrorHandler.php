<?php

namespace BO\Zmscitizenapi\Helper;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use BO\Slim\Response;
use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
   /**
    * Handle errors and exceptions in a standardized way.
    *
    * @param ServerRequestInterface $request           The current request
    * @param Throwable             $exception         The exception that was thrown
    * @param bool                  $displayErrorDetails Whether to display error details
    * @param bool                  $logErrors         Whether to log errors
    * @param bool                  $logErrorDetails   Whether to log error details
    */
   public function __invoke(
       ServerRequestInterface $request,
       Throwable $exception,
       bool $displayErrorDetails,
       bool $logErrors,
       bool $logErrorDetails
   ): ResponseInterface {
       $statusCode = 500;
       if ($exception instanceof HttpException) {
           $statusCode = $exception->getCode();
       }

       $error = [
           'message' => $this->getErrorMessage($exception, $displayErrorDetails),
           'code' => $statusCode,
       ];

       if ($displayErrorDetails) {
           $error['trace'] = $exception->getTraceAsString();
       }

       if ($logErrors) {
           $this->logError($exception, $logErrorDetails);
       }

       $payload = json_encode($error, JSON_PRETTY_PRINT);

       $response = new Response();
       $response->getBody()->write($payload);

       return $response
           ->withStatus($statusCode)
           ->withHeader('Content-Type', 'application/json');
   }

   /**
    * Get appropriate error message based on environment.
    */
   private function getErrorMessage(Throwable $exception, bool $displayErrorDetails): string
   {
       if ($displayErrorDetails) {
           return $exception->getMessage();
       }

       // Generic message in production
       return 'An error has occurred. Please try again later.';
   }

   /**
    * Log the error with appropriate detail level.
    */
   private function logError(Throwable $exception, bool $logErrorDetails): void
   {
       $message = $exception->getMessage();
       if ($logErrorDetails) {
           $message .= "\n" . $exception->getTraceAsString();
       }
       error_log($message);
   }
}
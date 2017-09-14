<?php

namespace BO\Zmsapi\Helper;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class TransactionMiddleware
{
    /**
     * @codeCoverageIgnore
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        \BO\Zmsdb\Connection\Select::setTransaction();
        if (null !== $next) {
            try {
                $response = $next($request, $response);
            } catch (\Exception $exception) {
                \BO\Zmsdb\Connection\Select::writeRollback();
                throw $exception;
            }
        }
        \BO\Zmsdb\Connection\Select::writeCommit();
        return $response;
    }
}

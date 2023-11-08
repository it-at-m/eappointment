<?php

namespace BO\Zmsapi\Helper;

use BO\Zmsdb\Connection\Select;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TransactionMiddleware
{
    /**
     * @codeCoverageIgnore
     *
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $next)
    {
        Select::setTransaction();
        try {
            $response = $next->handle($request);
        } catch (\Exception $exception) {
            Select::writeRollback();
            throw $exception;
        }
        Select::writeCommit();

        return $response;
    }
}

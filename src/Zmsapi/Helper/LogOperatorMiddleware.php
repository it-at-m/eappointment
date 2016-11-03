<?php

namespace BO\Zmsapi\Helper;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class LogOperatorMiddleware
{
    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $authority = $request->getUri()->getAuthority();
        \BO\Zmsdb\Log::$operator = $authority;
        if (null !== $next) {
            $response = $next($request, $response);
        }
        return $response;
    }
}

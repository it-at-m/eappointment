<?php

namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class Profiler
{
    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        \BO\Slim\Profiler::add("Routing");
        if (null !== $next) {
            $response = $next($request, $response);
        }
        //\BO\Slim\Profiler::add("Response");
        if (\App::DEBUG) {
            \BO\Slim\Profiler::addMemoryPeak();
            $response = $response->withAddedHeader('X-Profiling', \BO\Slim\Profiler::getList());
        }
        return $response;
    }
}

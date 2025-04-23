<?php

namespace BO\Slim\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BO\Slim\Factory\ResponseFactory;

class Profiler
{
    /**
     *
     */
    public function __invoke(ServerRequestInterface $request, ?RequestHandlerInterface $next): ResponseInterface
    {
        \BO\Slim\Profiler::add("Routing");
        if (null !== $next) {
            $response = $next->handle($request);
        } else {
            $response = (new ResponseFactory())->createResponse();
        }

        if (\App::DEBUG) {
            \BO\Slim\Profiler::addMemoryPeak();
            $response = $response->withAddedHeader('X-Profiling', \BO\Slim\Profiler::getList());
        }

        return $response;
    }
}

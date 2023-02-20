<?php

namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BO\Slim\Factory\ResponseFactory;

class Validator
{
    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ?RequestHandlerInterface $next
    ) {
        if (null !== $next) {
            $response = $next->handle(self::withValidator($request));
        } else {
            $response = (new ResponseFactory())->createResponse();
        }

        return $response;
    }

    public static function withValidator(ServerRequestInterface $request)
    {
        if ("GET" == $request->getMethod()) {
            $validator = new \BO\Mellon\Validator($request->getQueryParams());
        } else {
            $queryParams = $request->getQueryParams();
            $parameters = $request->getParsedBody();
            $parameters = is_array($parameters) ? $parameters : [];
            $parameters = array_merge($queryParams, $parameters);
            $validator = new \BO\Mellon\Validator($parameters);
            $body = (string)$request->getBody();
            $validator->setInput($body);
        }
        return $request->withAttribute('validator', $validator);
    }
}

<?php

namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class Validator
{
    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        if (null !== $next) {
            $response = $next(self::withValidator($request), $response);
        }
        return $response;
    }

    public static function withValidator(ServerRequestInterface $request)
    {
        if ("GET" == $request->getMethod()) {
            $validator = new \BO\Mellon\Validator($request->getQueryParams());
        } else {
            $parameters = $request->getParsedBody();
            $parameters = $parameters ? $parameters : [];
            $validator = new \BO\Mellon\Validator($parameters);
            $body = (string)$request->getBody();
            $validator->setInput($body);
        }
        return $request->withAttribute('validator', $validator);
    }
}

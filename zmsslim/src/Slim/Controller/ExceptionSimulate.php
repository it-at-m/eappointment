<?php

namespace BO\Slim\Controller;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class ExceptionSimulate extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        throw new \Exception("Unexpected exception simulation");
    }
}

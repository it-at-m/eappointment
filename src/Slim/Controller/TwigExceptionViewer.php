<?php

namespace BO\Slim\Controller;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class TwigExceptionViewer extends \BO\Slim\Controller
{
    /**
     *
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $exception = new \Exception($args['message']);
        $exception->template = $args['template'];
        $exception->data = $_REQUEST;
        return \BO\Slim\TwigExceptionHandler::withHtml($request, $response, $exception);
    }
}

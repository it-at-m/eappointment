<?php

namespace BO\Slim\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TwigExceptionViewer extends \BO\Slim\Controller
{
    /**
     * @SuppressWarnings(Superglobals)
     */
    #[\Override]
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $exception = new \Exception($args['message']);
        /** @psalm-suppress UndefinedPropertyAssignment Exception viewer passes template data to the HTML handler. */
        $exception->template = $args['template'];
        /** @psalm-suppress UndefinedPropertyAssignment Exception viewer passes request data to the HTML handler. */
        $exception->data = $_REQUEST;
        return \BO\Slim\TwigExceptionHandler::withHtml($request, $response, $exception);
    }
}

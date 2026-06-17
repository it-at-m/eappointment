<?php

namespace BO\Zmsstatistic\Helper;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TwigExceptionHandler extends \BO\Slim\TwigExceptionHandler
{
    #[\Override]
    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Throwable $exception,
        $status = 500
    ): ResponseInterface {
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            \BO\Slim\Controller::prepareRequest($request);
            return \BO\Slim\Render::withHtml($response, 'page/404.twig');
        }
        try {
            $exception->templatedata = [
                'workstation' => \App::$http->readGetResult('/workstation/')->getEntity(),
            ];
        } catch (\Throwable $workstationexception) {
            \App::$log->warning('Failed to fetch /workstation/ for extendedInfo', [
                'exception' => $workstationexception,
            ]);
        }

        return parent::withHtml($request, $response, $exception, $status);
    }
}

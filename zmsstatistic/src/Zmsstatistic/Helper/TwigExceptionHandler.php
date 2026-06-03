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
            \App::$http->readGetResult('/workstation/');
        } catch (\Throwable $workstationexception) {
            // ignore — error page should still render
        }

        return parent::withHtml($request, $response, $exception, $status);
    }

    #[\Override]
    public static function getExtendedExceptionInfo(\Throwable $exception, RequestInterface $request)
    {
        $extendedInfo = parent::getExtendedExceptionInfo($exception, $request);

        try {
            $workstationResult = \App::$http->readGetResult('/workstation/');
            if ($workstationResult) {
                $extendedInfo['workstation'] = $workstationResult->getEntity();
            }
        } catch (\Throwable $workstationexception) {
            // ignore
        }

        return $extendedInfo;
    }
}

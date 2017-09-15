<?php

namespace BO\Zmsstatistic\Helper;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class TwigExceptionHandler extends \BO\Slim\TwigExceptionHandler
{
    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $exception,
        $status = 500
    ) {
        try {
            $exception->templatedata = [
                'workstation' => \App::$http->readGetResult('/workstation/')->getEntity(),
            ];
        } catch (\Exception $workstationexception) {
            // ignore
        }

        return parent::withHtml($request, $response, $exception, $status);
    }
}

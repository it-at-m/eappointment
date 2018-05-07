<?php

namespace BO\Zmsadmin\Helper;

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
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
            $exception->templatedata = array(
                'workstation' => $workstation,
                'sourceData' => $exception->data
            );
        } catch (\Exception $workstationexception) {
            // ignore
        }
        return parent::withHtml($request, $response, $exception, $status);
    }
}

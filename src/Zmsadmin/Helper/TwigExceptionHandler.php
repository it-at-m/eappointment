<?php

namespace BO\Zmsadmin\Helper;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class TwigExceptionHandler extends \BO\Slim\TwigExceptionHandler
{
    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Throwable $exception,
        $status = 500
    ) {
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
            $data = [];
            if (isset($exception->data)) {
                $data = $exception->data;
            }
            $exception->templatedata = array('workstation' => $workstation, 'sourceData' => $data);
        } catch (\Throwable $workstationexception) {
            // ignore
        }
        return parent::withHtml($request, $response, $exception, $status);
    }
}

<?php

namespace BO\Zmsadmin\Helper;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TwigExceptionHandler extends \BO\Slim\TwigExceptionHandler
{
    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Throwable $exception,
        $status = 500
    ) {
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            \BO\Slim\Controller::prepareRequest($request);
            return \BO\Slim\Render::withHtml($response, 'page/404.twig');
        }
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
            $data = [];
            if (isset($exception->data)) {
                $data = $exception->data;
            }

            // Only set templatedata when the exception class declares the property,
            // to avoid PHP 8.2+ dynamic property deprecation warnings (e.g. Error, Twig\Error\RuntimeError).
            if (property_exists($exception, 'templatedata')) {
                $exception->templatedata = array('workstation' => $workstation, 'sourceData' => $data);
            }
        } catch (\Throwable $workstationexception) {
            // ignore
        }
        return parent::withHtml($request, $response, $exception, $status);
    }
}

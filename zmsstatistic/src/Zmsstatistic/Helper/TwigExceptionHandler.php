<?php

namespace BO\Zmsstatistic\Helper;

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
            // Only set templatedata when the exception class declares the property,
            // to avoid PHP 8.2+ dynamic property deprecation warnings (e.g. Twig\Error\RuntimeError).
            if (property_exists($exception, 'templatedata')) {
                $exception->templatedata = [
                    'workstation' => \App::$http->readGetResult('/workstation/')->getEntity(),
                ];
            }
        } catch (\Exception $workstationexception) {
            // ignore
        }

        return parent::withHtml($request, $response, $exception, $status);
    }
}

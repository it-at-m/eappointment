<?php

namespace BO\Zmsclient;

use BO\Zmsentities\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppointmentUrlMiddleware implements MiddlewareInterface
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        \App::$templatedefaults['urlAppointments'] = self::readAppointmentUrl();

        return $handler->handle($request);
    }

    public static function readAppointmentUrl(): string
    {
        try {
            $token = defined('App::CONFIG_SECURE_TOKEN') ? \App::CONFIG_SECURE_TOKEN : null;
            $entity = \App::$http->readGetResult('/config/', [], $token)->getEntity();
        } catch (\Throwable $exception) {
            \App::$log->error('Failed to read the appointment URL', [
                'error' => $exception->getMessage(),
            ]);
            return '';
        }

        if (!$entity instanceof Config) {
            return '';
        }

        $url = $entity->getPreference('appointments', 'urlAppointments');
        if (!is_string($url)) {
            return '';
        }

        return trim($url);
    }
}

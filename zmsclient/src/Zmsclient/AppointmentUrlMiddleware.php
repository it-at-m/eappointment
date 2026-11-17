<?php

namespace BO\Zmsclient;

use BO\Zmsentities\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppointmentUrlMiddleware implements MiddlewareInterface
{
    /**
     * Config loaded for this request with the service token.
     * Later handlers reuse it instead of calling /config/ again.
     */
    public const string CONFIG_ATTRIBUTE = 'zms.config';

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $config = self::readConfig();
        if ($config instanceof Config) {
            $request = $request->withAttribute(self::CONFIG_ATTRIBUTE, $config);
        }
        \App::$templatedefaults['urlAppointments'] = self::appointmentUrl($config);

        return $handler->handle($request);
    }

    public static function readAppointmentUrl(): string
    {
        return self::appointmentUrl(self::readConfig());
    }

    public static function readConfig(): ?Config
    {
        try {
            $token = defined('App::CONFIG_SECURE_TOKEN') ? \App::CONFIG_SECURE_TOKEN : null;
            $entity = \App::$http->readGetResult('/config/', [], $token)->getEntity();
        } catch (\Throwable $exception) {
            \App::$log->error('Failed to read the appointment URL', [
                'error' => $exception->getMessage(),
            ]);
            return null;
        }

        return $entity instanceof Config ? $entity : null;
    }

    private static function appointmentUrl(?Config $config): string
    {
        if (!$config instanceof Config) {
            return '';
        }

        $url = $config->getPreference('appointments', 'urlAppointments');
        if (!is_string($url)) {
            return '';
        }

        return trim($url);
    }
}

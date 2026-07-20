<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Calendar\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Calendar\Service\CalendarAvailability;
use BO\Zmsbackend\Slot\Exception\Calendar\InvalidAvailabilityInput;

class CalendarAvailabilityGet extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $t0 = microtime(true);
        $traceId = Validator::param('traceId')->isString()->getValue() ?: bin2hex(random_bytes(8));

        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        if ($slotType || $slotsRequired) {
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
        }

        try {
            $tAfterAuth = microtime(true);
            $message = \BO\Zmsbackend\Api\Response\Message::create($request);
            $message->data = (new CalendarAvailability())->readFromQuery(
                \App::getNow(),
                $slotType,
                $slotsRequired,
                Validator::param('startDate')->isString()->getValue(),
                Validator::param('endDate')->isString()->getValue(),
                Validator::param('officeId')->isString()->getValue(),
                Validator::param('serviceId')->isString()->getValue(),
                Validator::param('serviceCount')->isString()->setDefault('')->getValue(),
                Validator::param('providerSource')->isString()->getValue() ?: null,
                Validator::param('requestSource')->isString()->getValue() ?: null,
                $traceId,
                Validator::param('slotsStartDate')->isString()->getValue(),
                Validator::param('slotsEndDate')->isString()->getValue()
            );
            $tAfterService = microtime(true);
        } catch (InvalidAvailabilityInput $exception) {
            throw new \BO\Zmsbackend\Calendar\Exception\InvalidFirstDay(
                $exception->getMessage(),
                0,
                $exception
            );
        }

        $response = Render::withLastModified($response, time(), '0');
        $responseOut = Render::withJson($response, $message->setUpdatedMetaData(), 200);

        if (\App::$log) {
            \App::$log->info('calendar.availability.timing', [
                'trace_id' => $traceId,
                'stage' => 'api.CalendarAvailabilityGet',
                'auth_ms' => (int) round(($tAfterAuth - $t0) * 1000),
                'service_ms' => (int) round(($tAfterService - $tAfterAuth) * 1000),
                'json_ms' => (int) round((microtime(true) - $tAfterService) * 1000),
                'total_ms' => (int) round((microtime(true) - $t0) * 1000),
            ]);
        }

        return $responseOut;
    }
}

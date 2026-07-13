<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\CalendarAvailability as CalendarAvailabilityQuery;
use BO\Zmsdb\Exception\Calendar\InvalidAvailabilityInput;

class CalendarAvailabilityGet extends BaseController
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
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        if ($slotType || $slotsRequired) {
            (new Helper\User($request))->checkPermissions();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
        }

        try {
            $message = Response\Message::create($request);
            $message->data = (new CalendarAvailabilityQuery())->readFromQuery(
                \App::getNow(),
                $slotType,
                $slotsRequired,
                Validator::param('startDate')->isString()->getValue(),
                Validator::param('endDate')->isString()->getValue(),
                Validator::param('officeId')->isString()->getValue(),
                Validator::param('serviceId')->isString()->getValue(),
                Validator::param('serviceCount')->isString()->setDefault('')->getValue(),
                Validator::param('providerSource')->isString()->getValue() ?: null,
                Validator::param('requestSource')->isString()->getValue() ?: null
            );
        } catch (InvalidAvailabilityInput $exception) {
            throw new Exception\Calendar\InvalidFirstDay($exception->getMessage(), 0, $exception);
        }

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message->setUpdatedMetaData(), 200);
    }
}

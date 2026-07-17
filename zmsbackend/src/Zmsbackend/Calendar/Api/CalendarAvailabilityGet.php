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
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        if ($slotType || $slotsRequired) {
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
        }

        try {
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
                Validator::param('requestSource')->isString()->getValue() ?: null
            );
        } catch (InvalidAvailabilityInput $exception) {
            throw new \BO\Zmsbackend\Calendar\Exception\InvalidFirstDay(
                $exception->getMessage(),
                0,
                $exception
            );
        }

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message->setUpdatedMetaData(), 200);
    }
}

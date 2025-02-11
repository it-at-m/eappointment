<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Calendar as Query;

class CalendarGet extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $hasGQL = Validator::param('gql')->isString()->getValue();
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        if ($slotType || $slotsRequired) {
            (new Helper\User($request))->checkRights();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
        }

        $query = new Query();
        $fillWithEmptyDays = Validator::param('fillWithEmptyDays')->isNumber()->setDefault(0)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $calendar = new \BO\Zmsentities\Calendar($input);

        $message = Response\Message::create($request);

        if (!$calendar->hasFirstAndLastDay()) {
            throw new Exception\Calendar\InvalidFirstDay('First and last day are required');
        } else {
            $calendar = $query
              ->readResolvedEntity($calendar, \App::getNow(), null, $slotType, $slotsRequired);
              $calendar = ($hasGQL) ? $calendar : $calendar->withLessData();
            if ($fillWithEmptyDays) {
                $calendar = $calendar->withFilledEmptyDays();
            }
            $calendar->days = $calendar->days->withDaysInDateRange($calendar->getFirstDay(), $calendar->getLastDay());
            $message->data = $calendar;
        }
        if (0 == count($message->data['days'])) {
            $exception = new Exception\Calendar\AppointmentsMissed();
            $exception->data = $message->data;
            throw $exception;
        }
        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin\Helper;

use BO\Slim\Render;
use BO\Zmsadmin\BaseController;
use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use DateTimeImmutable;

class AvailabilityConflicts extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();
        $data = static::getAvailabilityData($input);
        return Render::withJson(
            $response,
            $data
        );
    }

    protected static function getAvailabilityData($input)
    {
        $availabilityList = (new AvailabilityList())->addData($input['availabilityList']);
        $conflictedList = [];

        $selectedDateTime = (new DateTimeImmutable($input['selectedDate']))->modify(\App::$now->format('H:i:s'));
        $selectedAvailability = new Availability($input['selectedAvailability']);
        $startDateTime = ($selectedAvailability->getStartDateTime() >= \App::$now) ?
            $selectedAvailability->getStartDateTime() : $selectedDateTime;
        $endDateTime = ($input['selectedAvailability']) ?
            $selectedAvailability->getEndDateTime() : $selectedDateTime;

        $availabilityList = $availabilityList->sortByCustomStringKey('endTime');
        $conflictList = $availabilityList->getConflicts($startDateTime, $endDateTime);

        foreach ($conflictList as $conflict) {
            $availabilityId = ($conflict->getFirstAppointment()->getAvailability()->getId()) ?
                $conflict->getFirstAppointment()->getAvailability()->getId() :
                $conflict->getFirstAppointment()->getAvailability()->tempId;
            if (! in_array($availabilityId, $conflictedList)) {
                $conflictedList[] = $availabilityId;
            }
        }

        return [
            'conflictList' => $conflictList->toConflictListByDay(),
            'conflictIdList' => (count($conflictedList)) ? $conflictedList : []
        ];
    }

}

<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Availability;

use BO\Zmsentities\Collection\AvailabilityList;

/**
 * Check if new Availability is in conflict with existing availability
 *
 */
class AvailabilityConflicts extends BaseController
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
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();
        $data = static::getAvailabilityData($input);
        return \BO\Slim\Render::withJson(
            $response,
            $data
        );
    }

    protected static function getAvailabilityData($input)
    {
        $conflictList = new \BO\Zmsentities\Collection\ProcessList();
        $availabilityList = (new AvailabilityList())->addData($input['availabilityList']);
        $conflictedList = [];

        $selectedDateTime = (new \DateTimeImmutable($input['selectedDate']))->modify(\App::$now->format('H:i:s'));
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

    /*
    protected static function getAvailabilityList($scope, $dateTime)
    {
        try {
            $availabilityList = \App::$http
                ->readGetResult(
                    '/scope/' . $scope->getId() . '/availability/',
                    [
                        'resolveReferences' => 0,
                        'startDate' => $dateTime->format('Y-m-d') //for skipping old availabilities
                    ]
                )
                ->getCollection();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound') {
                throw $exception;
            }
            $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList();
        }
        return $availabilityList->withScope($scope);
    }
    */
}

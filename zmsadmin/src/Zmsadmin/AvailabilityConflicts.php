<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;
use DateTimeImmutable;

/**
 * Check if new Availability is in conflict with existing availability
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
        error_log('$input :' . json_encode($input));
        
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

        // Extract selected date and selected availability
        $selectedDate = new DateTimeImmutable($input['selectedDate']);
        $selectedAvailability = new Availability($input['selectedAvailability']);
        $selectedDateTime = $selectedDate->setTime(0, 0);

        // Determine start and end times based on selectedAvailability
        $startDateTime = $selectedAvailability->getStartDateTime() < $selectedDateTime ? $selectedDateTime : $selectedAvailability->getStartDateTime();
        $endDateTime = $selectedAvailability->getEndDateTime();

        // Prepare a merged collection with existing and new availabilities
        $scopeData = $input['availabilityList'][0]['scope'];
        $scope = new \BO\Zmsentities\Scope($scopeData);
        $availabilityRepo = new \BO\Zmsdb\Availability();
        $existingCollection = $availabilityRepo->readAvailabilityListByScope($scope, 1);

        $mergedCollection = new AvailabilityList();
        foreach ($existingCollection as $existingAvailability) {
            $mergedCollection->addEntity($existingAvailability);
        }

        // Add the selected availability to the merged collection
        $mergedCollection->addEntity($selectedAvailability);

        // Get the earliest and latest dates for the conflict range
        [$earliestStartDateTime, $latestEndDateTime] = static::getDateTimeRangeFromCollection($mergedCollection, $selectedDateTime);

        // Check for conflicts in the merged collection within the computed range
        $conflictList = $mergedCollection->getConflicts($earliestStartDateTime, $latestEndDateTime);

        // Extract conflicting IDs
        foreach ($conflictList as $conflict) {
            $availabilityId = ($conflict->getFirstAppointment()->getAvailability()->getId()) ?
                $conflict->getFirstAppointment()->getAvailability()->getId() :
                $conflict->getFirstAppointment()->getAvailability()->tempId;
            if (!in_array($availabilityId, $conflictedList)) {
                $conflictedList[] = $availabilityId;
            }
        }

        error_log(json_encode($conflictedList));

        return [
            'conflictList' => $conflictList->toConflictListByDay(),
            'conflictIdList' => (count($conflictedList)) ? $conflictedList : []
        ];
    }

    /**
     * Get the earliest startDateTime and latest endDateTime from a Collection
     * If the start date of any availability is before the selected date, 
     * use the selected date instead.
     *
     * @param AvailabilityList $collection
     * @param \DateTimeImmutable $selectedDate
     * @return array
     */
    private static function getDateTimeRangeFromCollection(AvailabilityList $collection, DateTimeImmutable $selectedDate): array
    {
        $earliestStartDateTime = null;
        $latestEndDateTime = null;

        foreach ($collection as $availability) {
            // Convert Unix timestamp to a date string before concatenating with the time
            $startDate = (new DateTimeImmutable())->setTimestamp($availability->startDate)->format('Y-m-d');
            $endDate = (new DateTimeImmutable())->setTimestamp($availability->endDate)->format('Y-m-d');

            // Combine date and time for start and end
            $startDateTime = new DateTimeImmutable("{$startDate} {$availability->startTime}");
            $endDateTime = new DateTimeImmutable("{$endDate} {$availability->endTime}");

            // If startDate is before the selectedDate, use the selectedDate as the start
            if ($startDateTime < $selectedDate) {
                $startDateTime = $selectedDate->setTime(0, 0);
            }

            // Determine the earliest start and latest end times
            if (is_null($earliestStartDateTime) || $startDateTime < $earliestStartDateTime) {
                $earliestStartDateTime = $startDateTime;
            }
            if (is_null($latestEndDateTime) || $endDateTime > $latestEndDateTime) {
                $latestEndDateTime = $endDateTime;
            }
        }

        return [$earliestStartDateTime, $latestEndDateTime];
    }
}

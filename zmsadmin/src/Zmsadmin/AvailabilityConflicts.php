<?php

namespace BO\Zmsadmin;

use BO\Zmsadmin\Exception\BadRequest as BadRequestException;
use BO\Zmsentities\Collection\AvailabilityList;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Scope;
use DateTimeImmutable;

class AvailabilityConflicts extends BaseController
{
    const CONFLICT_TYPE_EQUAL = 'equal';
    const CONFLICT_TYPE_OVERLAP = 'overlap';

    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();
        $data = static::getAvailabilityData($input);
        return \BO\Slim\Render::withJson($response, $data);
    }

    protected static function getAvailabilityData($input)
    {
        self::validateInput($input);

        $availabilityList = (new AvailabilityList())->addData($input['availabilityList']);
        $selectedDateTime = (new DateTimeImmutable($input['selectedDate']))
            ->modify(\App::$now->format('H:i:s'));

        [$hasExclusionSplit, $originId] = self::processAvailabilityKinds($availabilityList);

        $conflictList = self::getConflictList(
            $availabilityList,
            $selectedDateTime,
            $input,
            $hasExclusionSplit,
            $originId
        );


        return self::filterAndSortConflicts($conflictList, $selectedDateTime);
    }

    private static function validateInput($input)
    {
        if (!isset($input['availabilityList']) || !is_array($input['availabilityList'])) {
            throw new BadRequestException('Missing or invalid availabilityList.');
        }
        if (empty($input['availabilityList']) || !isset($input['availabilityList'][0]['scope'])) {
            throw new BadRequestException('Missing or invalid scope.');
        }
        if (!isset($input['selectedDate'])) {
            throw new BadRequestException("'selectedDate' is required.");
        }
    }

    private static function processAvailabilityKinds(AvailabilityList $availabilityList)
    {
        $hasExclusionSplit = false;
        $originId = null;
        foreach ($availabilityList as $availability) {
            if (!isset($availability->kind)) {
                continue;
            }

            if ($availability->kind === 'origin' && isset($availability->id)) {
                $originId = $availability->id;
                $hasExclusionSplit = true;
            } elseif (in_array($availability->kind, ['origin', 'exclusion', 'future'])) {
                $hasExclusionSplit = true;
            }
        }
        return [$hasExclusionSplit, $originId];
    }

    private static function getConflictList(
        AvailabilityList $availabilityList,
        DateTimeImmutable $selectedDateTime,
        array $input,
        bool $hasExclusionSplit,
        ?string $originId
    ) {
        $conflictList = new ProcessList();

        $overlapConflicts = $availabilityList->checkForConflictsBetweenNewAvailabilities();
        $conflictList->addList($overlapConflicts);

        $scope = new Scope($input['availabilityList'][0]['scope']);
        $existingAvailabilityList = self::getAvailabilityList($scope, $selectedDateTime);

        $filteredAvailabilityList = self::getFilteredAvailabilityList(
            $availabilityList,
            $existingAvailabilityList,
            $hasExclusionSplit,
            $originId
        );

        [$earliestStartDateTime, $latestEndDateTime] = $filteredAvailabilityList
            ->getDateTimeRangeFromList();
        $filteredAvailabilityList = $filteredAvailabilityList->sortByCustomStringKey('endTime');

        $existingConflicts = $filteredAvailabilityList->checkForConflictsWithExistingAvailabilities(
            $earliestStartDateTime,
            $latestEndDateTime
        );
        $conflictList->addList($existingConflicts);

        return $conflictList;
    }

    private static function getFilteredAvailabilityList(
        AvailabilityList $availabilityList,
        AvailabilityList $existingAvailabilityList,
        bool $hasExclusionSplit,
        ?string $originId
    ) {
        $filteredAvailabilityList = new AvailabilityList();

        foreach ($availabilityList as $availability) {
            $filteredAvailabilityList->addEntity($availability);
        }

        foreach ($existingAvailabilityList as $existingAvailability) {
            if (
                $hasExclusionSplit &&
                isset($existingAvailability->id) &&
                $existingAvailability->id === $originId
            ) {
                continue;
            }

            $filteredAvailabilityList->addEntity($existingAvailability);
        }

        return $filteredAvailabilityList;
    }

    private static function filterAndSortConflicts(ProcessList $conflictList, $selectedDate)
    {
        $selectedDate = new DateTimeImmutable($selectedDate->format('Y-m-d'));

        $filteredConflictList = new ProcessList();
        $conflictedList = [];
        $processedConflicts = [];
        $manualDayGrouping = [];

        foreach ($conflictList as $conflict) {
            if (!$conflict->getFirstAppointment() || !$conflict->getFirstAppointment()->getAvailability()) {
                continue;
            }

            $appointment = $conflict->getFirstAppointment();
            $availability = $appointment->getAvailability();
            $availId = $availability->getId() ?: $availability->tempId;

            if (preg_match_all('/\[([^,]+), (\d{2}:\d{2} - \d{2}:\d{2})/', $conflict->amendment, $matches)) {
                $dateRanges = $matches[1];
                $timeRanges = $matches[2];

                $times = [$timeRanges[0], $timeRanges[1]];
                sort($times);
                $conflictKey = $availId . '_' . md5($dateRanges[0] . implode('', $times));

                if (isset($processedConflicts[$conflictKey])) {
                    continue;
                }

                $conflictDate = clone $selectedDate;

                $dateKey = $conflictDate->format('Y-m-d');

                if (!isset($manualDayGrouping[$dateKey])) {
                    $manualDayGrouping[$dateKey] = [];
                }

                $appointments = iterator_to_array($conflict->appointments);
                $manualDayGrouping[$dateKey][] = [
                    'message' => $conflict->amendment,
                    'appointments' => array_map(function ($appt) {
                        return [
                            'startTime' => $appt->getStartTime()->format('H:i'),
                            'endTime' => $appt->getEndTime()->format('H:i'),
                            'availability' => $appt->getAvailability()->getId() ?: $appt->getAvailability()->tempId
                        ];
                    }, $appointments)
                ];

                $processedConflicts[$conflictKey] = true;
                $filteredConflictList->addEntity($conflict);
                self::addToConflictedList($conflictedList, $availability, null);
            }
        }

        usort($conflictedList, [self::class, 'sortConflictedList']);

        return [
            'conflictList' => $manualDayGrouping,
            'conflictIdList' => (count($conflictedList)) ? $conflictedList : []
        ];
    }

    private static function findMatchingAvailability($conflict, $filteredAvailabilityList)
    {
        $availability1 = $conflict->getFirstAppointment()->getAvailability();
        foreach ($filteredAvailabilityList as $avail) {
            if (
                $avail->id === $availability1->id ||
                (isset($avail->tempId) && isset($availability1->tempId) &&
                    $avail->tempId === $availability1->tempId)
            ) {
                return $avail;
            }
        }
        return null;
    }

    private static function doesConflictAffectWeekday($availability1, $availability2, $weekdayKey)
    {
        if (
            isset($availability1->weekday[$weekdayKey]) &&
            (int) $availability1->weekday[$weekdayKey] > 0
        ) {
            return true;
        }

        if (
            $availability2 && isset($availability2->weekday[$weekdayKey]) &&
            (int) $availability2->weekday[$weekdayKey] > 0
        ) {
            return true;
        }

        return false;
    }

    private static function addToConflictedList(&$conflictedList, $availability1, $availability2)
    {
        $availabilityId = $availability1->getId() ?: $availability1->tempId;
        if (!in_array($availabilityId, $conflictedList)) {
            $conflictedList[] = $availabilityId;
        }

        if ($availability2) {
            $availabilityId2 = $availability2->getId() ?: $availability2->tempId;
            if (!in_array($availabilityId2, $conflictedList)) {
                $conflictedList[] = $availabilityId2;
            }
        }
    }

    /** @SuppressWarnings(PHPMD.UnusedPrivateMethod) */
    private static function sortConflictedList($a, $b)
    {
        $aIsTemp = strpos($a, '__temp__') === 0;
        $bIsTemp = strpos($b, '__temp__') === 0;

        if ($aIsTemp && !$bIsTemp) {
            return 1;
        }
        if (!$aIsTemp && $bIsTemp) {
            return -1;
        }
        return strcmp($a, $b);
    }

    protected static function getAvailabilityList($scope, $dateTime)
    {
        try {
            $availabilityList = \App::$http
                ->readGetResult(
                    '/scope/' . $scope->getId() . '/availability/',
                    [
                        'resolveReferences' => 0,
                        'startDate' => $dateTime->format('Y-m-d')
                    ]
                )
                ->getCollection();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound') {
                throw $exception;
            }
            $availabilityList = new AvailabilityList();
        }
        return $availabilityList->withScope($scope);
    }
}

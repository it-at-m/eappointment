<?php

namespace BO\Zmsadmin;

use BO\Zmsadmin\Exception\BadRequest as BadRequestException;
use BO\Zmsentities\Collection\AvailabilityList;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Scope;
use DateTimeImmutable;

class AvailabilityConflicts extends BaseController
{
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
        
        // Add conflicts between new availabilities
        $overlapConflicts = $availabilityList->hasNewVsNewConflicts($selectedDateTime);
        $conflictList->addList($overlapConflicts);

        // Get and filter existing availabilities
        $scope = new Scope($input['availabilityList'][0]['scope']);
        $futureAvailabilityList = self::getAvailabilityList($scope, $selectedDateTime);
        
        $filteredAvailabilityList = self::getFilteredAvailabilityList(
            $availabilityList,
            $futureAvailabilityList,
            $hasExclusionSplit,
            $originId
        );

        // Add conflicts with existing availabilities
        [$earliestStartDateTime, $latestEndDateTime] = $filteredAvailabilityList
            ->getDateTimeRangeFromList($selectedDateTime);
        $filteredAvailabilityList = $filteredAvailabilityList->sortByCustomStringKey('endTime');
        
        $existingConflicts = $filteredAvailabilityList->checkAllVsExistingConflicts(
            $earliestStartDateTime,
            $latestEndDateTime
        );
        $conflictList->addList($existingConflicts);

        return $conflictList;
    }

    private static function getFilteredAvailabilityList(
        AvailabilityList $availabilityList,
        AvailabilityList $futureAvailabilityList,
        bool $hasExclusionSplit,
        ?string $originId
    ) {
        $filteredAvailabilityList = new AvailabilityList();
        
        foreach ($availabilityList as $availability) {
            $isSpecialKind = isset($availability->kind) && 
                in_array($availability->kind, ['origin', 'exclusion', 'future']);

            foreach ($futureAvailabilityList as $futureAvailability) {
                if (!$isSpecialKind || !$hasExclusionSplit || 
                    !isset($futureAvailability->id) || 
                    $futureAvailability->id !== $originId
                ) {
                    $filteredAvailabilityList->addEntity($futureAvailability);
                }
            }

            $filteredAvailabilityList->addEntity($availability);
        }

        return $filteredAvailabilityList;
    }

    private static function filterAndSortConflicts(ProcessList $conflictList, DateTimeImmutable $selectedDateTime)
    {
        $weekday = (int)$selectedDateTime->format('N');
        $weekdayKey = strtolower(date('l', strtotime("Sunday +{$weekday} days")));
        
        $filteredConflictList = new ProcessList();
        $conflictedList = [];

        foreach ($conflictList as $conflict) {
            $availability1 = $conflict->getFirstAppointment()->getAvailability();
            $availability2 = self::findMatchingAvailability($conflict, $conflictList);

            if (self::doesConflictAffectWeekday($availability1, $availability2, $weekdayKey)) {
                $filteredConflictList->addEntity($conflict);
                self::addToConflictedList($conflictedList, $availability1, $availability2);
            }
        }

        usort($conflictedList, [self::class, 'sortConflictedList']);

        return [
            'conflictList' => $filteredConflictList->toConflictListByDay(),
            'conflictIdList' => (count($conflictedList)) ? $conflictedList : []
        ];
    }

    private static function findMatchingAvailability($conflict, $filteredAvailabilityList)
    {
        $availability1 = $conflict->getFirstAppointment()->getAvailability();
        foreach ($filteredAvailabilityList as $avail) {
            if ($avail->id === $availability1->id ||
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
        if (isset($availability1->weekday[$weekdayKey]) && 
            (int)$availability1->weekday[$weekdayKey] > 0
        ) {
            return true;
        }
        
        if ($availability2 && isset($availability2->weekday[$weekdayKey]) && 
            (int)$availability2->weekday[$weekdayKey] > 0
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
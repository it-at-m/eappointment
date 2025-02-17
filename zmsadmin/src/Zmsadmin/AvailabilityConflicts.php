<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsadmin\Exception\BadRequest as BadRequestException;
use BO\Zmsentities\Collection\AvailabilityList;

/**
 * Check if new Availability is in conflict with existing availability
 */
class AvailabilityConflicts extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return string
     */
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
        if (!isset($input['availabilityList']) || !is_array($input['availabilityList'])) {
            throw new BadRequestException('Missing or invalid availabilityList.');
        } elseif (empty($input['availabilityList']) || !isset($input['availabilityList'][0]['scope'])) {
            throw new BadRequestException('Missing or invalid scope.');
        } elseif (!isset($input['selectedDate'])) {
            throw new BadRequestException("'selectedDate' is required.");
        }

        $conflictedList = [];
        $availabilityList = (new AvailabilityList())->addData($input['availabilityList']);
        $selectedDateTime = (new \DateTimeImmutable($input['selectedDate']))->modify(\App::$now->format('H:i:s'));
        $weekday = (int)$selectedDateTime->format('N');

        $hasExclusionSplit = false;
        $originId = null;
        foreach ($availabilityList as $availability) {
            if (isset($availability->kind)) {
                if ($availability->kind === 'origin' && isset($availability->id)) {
                    $originId = $availability->id;
                    $hasExclusionSplit = true;
                } elseif (in_array($availability->kind, ['origin', 'exclusion', 'future'])) {
                    $hasExclusionSplit = true;
                } elseif (in_array($availability->kind, ['origin', 'exclusion'])) {
                    $hasExclusionSplit = true;
                } elseif (in_array($availability->kind, ['origin', 'future'])) {
                    $hasExclusionSplit = true;
                }
            }
        }

        $conflictList = new \BO\Zmsentities\Collection\ProcessList();
        $overlapConflicts = $availabilityList->hasNewVsNewConflicts($selectedDateTime);
        $conflictList->addList($overlapConflicts);

        $scopeData = $input['availabilityList'][0]['scope'];
        $scope = new \BO\Zmsentities\Scope($scopeData);
        $futureAvailabilityList = self::getAvailabilityList($scope, $selectedDateTime);

        $filteredAvailabilityList = new AvailabilityList();
        foreach ($availabilityList as $availability) {
            $isSpecialKind = isset($availability->kind) && in_array($availability->kind, ['origin', 'exclusion', 'future']);

            foreach ($futureAvailabilityList as $futureAvailability) {
                if (!$isSpecialKind || !$hasExclusionSplit || !isset($futureAvailability->id) || $futureAvailability->id !== $originId) {
                    $filteredAvailabilityList->addEntity($futureAvailability);
                }
            }

            $filteredAvailabilityList->addEntity($availability);
        }

        [$earliestStartDateTime, $latestEndDateTime] = $filteredAvailabilityList->getDateTimeRangeFromList($selectedDateTime);
        $filteredAvailabilityList = $filteredAvailabilityList->sortByCustomStringKey('endTime');
        $existingConflicts = $filteredAvailabilityList->checkAllVsExistingConflicts($earliestStartDateTime, $latestEndDateTime);
        $conflictList->addList($existingConflicts);

        $filteredConflictList = new \BO\Zmsentities\Collection\ProcessList();
        foreach ($conflictList as $conflict) {
            $availability1 = $conflict->getFirstAppointment()->getAvailability();
            $availability2 = null;
            foreach ($filteredAvailabilityList as $avail) {
                if (
                    $avail->id === $availability1->id ||
                    (isset($avail->tempId) && isset($availability1->tempId) && $avail->tempId === $availability1->tempId)
                ) {
                    $availability2 = $avail;
                    break;
                }
            }

            $affectsSelectedDay = false;
            $weekdayKey = strtolower(date('l', strtotime("Sunday +{$weekday} days")));

            if (isset($availability1->weekday[$weekdayKey]) && (int)$availability1->weekday[$weekdayKey] > 0) {
                $affectsSelectedDay = true;
            }
            if ($availability2 && isset($availability2->weekday[$weekdayKey]) && (int)$availability2->weekday[$weekdayKey] > 0) {
                $affectsSelectedDay = true;
            }

            if ($affectsSelectedDay) {
                $filteredConflictList->addEntity($conflict);
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
        }

        usort($conflictedList, function ($a, $b) {
            $aIsTemp = strpos($a, '__temp__') === 0;
            $bIsTemp = strpos($b, '__temp__') === 0;
            if ($aIsTemp && !$bIsTemp) {
                return 1;
            }
            if (!$aIsTemp && $bIsTemp) {
                return -1;
            }
            return strcmp($a, $b);
        });

        return [
            'conflictList' => $filteredConflictList->toConflictListByDay(),
            'conflictIdList' => (count($conflictedList)) ? $conflictedList : []
        ];
    }

    /**
     * Fetch availabilities for a given scope and date.
     *
     * @param \BO\Zmsentities\Scope $scope
     * @param \DateTimeImmutable $dateTime
     * @return AvailabilityList
     */
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

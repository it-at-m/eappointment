<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsapi\Exception\BadRequest as BadRequestException;
use BO\Zmsentities\Collection\AvailabilityList;
use BO\Zmsentities\Process;

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
        $data = static::getAvailabilityData($input);
        return \BO\Slim\Render::withJson($response, $data);
    }

    protected static function getAvailabilityData($input)
    {
        if (!isset($input['availabilityList']) || !is_array($input['availabilityList'])) {
            throw new BadRequestException('Missing or invalid availabilityList.');
        } else if(!isset($input['availabilityList'][0]['scope'])){
            throw new BadRequestException('Missing or invalid scope.');
        } else if (!isset($input['selectedDate'])) {
            throw new BadRequestException("'selectedDate' is required.");
        }

        $conflictList = new \BO\Zmsentities\Collection\ProcessList();
        $availabilityList = (new AvailabilityList())->addData($input['availabilityList']);
        $conflictedList = [];
    
        $selectedDateTime = (new \DateTimeImmutable($input['selectedDate']))->modify(\App::$now->format('H:i:s'));

        $overlapConflicts = self::checkNewVsNewConflicts($availabilityList, $selectedDateTime);
        $conflictList->addList($overlapConflicts);
    
        $scopeData = $input['availabilityList'][0]['scope'];
        $scope = new \BO\Zmsentities\Scope($scopeData);

        $futureAvailabilityList = self::getAvailabilityList($scope, $selectedDateTime);
    
        foreach ($futureAvailabilityList as $futureAvailability) {
            $availabilityList->addEntity($futureAvailability);
        }
    
        $originId = null;
        foreach ($availabilityList as $availability) {
            if (isset($availability->kind) && $availability->kind === 'origin' && isset($availability->id)) {
                $originId = $availability->id;
                break;
            }
        }
    
        $filteredAvailabilityList = new AvailabilityList();
        foreach ($availabilityList as $availability) {
            if ((!isset($availability->kind) || $availability->kind !== 'exclusion') && 
                (!isset($availability->id) || $availability->id !== $originId)) {
                $filteredAvailabilityList->addEntity($availability);
            }
        }
    
        [$earliestStartDateTime, $latestEndDateTime] = $filteredAvailabilityList->getDateTimeRangeFromList($selectedDateTime);
    
        $filteredAvailabilityList = $filteredAvailabilityList->sortByCustomStringKey('endTime');
        $existingConflicts = $filteredAvailabilityList->checkAllVsExistingConflicts($earliestStartDateTime, $latestEndDateTime);
        $conflictList->addList($existingConflicts);
    
        foreach ($conflictList as $conflict) {
            $availabilityId = ($conflict->getFirstAppointment()->getAvailability()->getId()) ?
                $conflict->getFirstAppointment()->getAvailability()->getId() :
                $conflict->getFirstAppointment()->getAvailability()->tempId;
            if (!in_array($availabilityId, $conflictedList)) {
                $conflictedList[] = $availabilityId;
            }
        }
    
        return [
            'conflictList' => $conflictList->toConflictListByDay(),
            'conflictIdList' => (count($conflictedList)) ? $conflictedList : []
        ];
    }

    /**
     * Check for overlaps between availabilities in the collection
     * 
     * @param AvailabilityList $collection
     * @param \DateTimeImmutable $selectedDateTime
     * @return \BO\Zmsentities\Collection\ProcessList
     */
    protected static function checkNewVsNewConflicts(AvailabilityList $collection, \DateTimeImmutable $selectedDateTime)
    {
        $conflicts = new \BO\Zmsentities\Collection\ProcessList();
        
        $newAvailabilities = new AvailabilityList();
        foreach ($collection as $availability) {
            if (isset($availability->tempId) && strpos($availability->tempId, '__temp__') !== false) {
                $newAvailabilities->addEntity($availability);
            }
        }
        
        foreach ($newAvailabilities as $availability1) {
            foreach ($newAvailabilities as $availability2) {
                $scope1Id = is_array($availability1->scope) ? ($availability1->scope['id'] ?? null) : ($availability1->scope->id ?? null);
                $scope2Id = is_array($availability2->scope) ? ($availability2->scope['id'] ?? null) : ($availability2->scope->id ?? null);
                
                if ($availability1 !== $availability2 && 
                    $availability1->type == $availability2->type &&
                    $scope1Id == $scope2Id &&
                    $availability1->hasSharedWeekdayWith($availability2)) {
                    
                    $date1 = (new \DateTimeImmutable())->setTimestamp($availability1->startDate)->format('Y-m-d');
                    $date2 = (new \DateTimeImmutable())->setTimestamp($availability2->startDate)->format('Y-m-d');
                    
                    if ($date1 === $date2) {
                        $time1Start = (new \DateTimeImmutable())->setTimestamp($availability1->startDate)
                            ->modify($availability1->startTime)->format('H:i');
                        $time1End = (new \DateTimeImmutable())->setTimestamp($availability1->endDate)
                            ->modify($availability1->endTime)->format('H:i');
                        $time2Start = (new \DateTimeImmutable())->setTimestamp($availability2->startDate)
                            ->modify($availability2->startTime)->format('H:i');
                        $time2End = (new \DateTimeImmutable())->setTimestamp($availability2->endDate)
                            ->modify($availability2->endTime)->format('H:i');
    
                        if ($time2Start < $time1End && $time1Start < $time2End) {
                            $process = new Process();
                            
                            $dateRange1 = date('d.m.Y', $availability1->startDate) . ' - ' . date('d.m.Y', $availability1->endDate);
                            $dateRange2 = date('d.m.Y', $availability2->startDate) . ' - ' . date('d.m.Y', $availability2->endDate);
                            $timeRange1 = $time1Start . ' - ' . $time1End;
                            $timeRange2 = $time2Start . ' - ' . $time2End;
    
                            $process->amendment = "Konflikt: Zwei Öffnungszeiten überschneiden sich.\n"
                                . "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$dateRange1, $timeRange1]\n"
                                . "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$dateRange2, $timeRange2]";
                            
                            $appointment = new \BO\Zmsentities\Appointment();
                            $appointment->date = $availability1->startDate;
                            $appointment->availability = $availability1;
                            $process->addAppointment($appointment);
                            $conflicts->addEntity($process);
                        }
                    }
                }
            }
        }
        error_log(json_encode("Conflicts: " . $conflicts));
        return $conflicts;
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
            $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList();
        }
        return $availabilityList->withScope($scope);
    }
}
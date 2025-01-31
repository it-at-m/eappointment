<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsapi\Exception\BadRequest as BadRequestException;
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
        } else if(!isset($input['availabilityList'][0]['scope'])){
            throw new BadRequestException('Missing or invalid scope.');
        } else if (!isset($input['selectedDate'])) {
            throw new BadRequestException("'selectedDate' is required.");
        }

        $conflictedList = [];
        
        $availabilityList = (new AvailabilityList())->addData($input['availabilityList']);
        $selectedDateTime = (new \DateTimeImmutable($input['selectedDate']))->modify(\App::$now->format('H:i:s'));

        $conflictList = new \BO\Zmsentities\Collection\ProcessList();
        $overlapConflicts = $availabilityList->hasNewVsNewConflicts($selectedDateTime);
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
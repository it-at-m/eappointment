<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Availability;

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
        $entity = new Availability($input);
        $data = static::getAvailabilityData($entity);
        return \BO\Slim\Render::withJson(
            $response,
            $data
        );
    }

    protected static function getAvailabilityData($entity)
    {
        $scope = new \BO\Zmsentities\Scope($entity->scope);
        $startDate = $entity->getStartDateTime();
        $endDate = $entity->getEndDateTime();
        $availabilityList = static::getAvailabilityList($scope, $startDate);
        $availabilityList->addEntity($entity);
        $conflictList = $availabilityList->getConflicts($startDate, $endDate);
        return [
            'conflictList' => $conflictList->toConflictListByDay()
        ];
    }

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
        return $availabilityList->withScope($scope)->withDateTime($dateTime); //withDateTime to check if opened
    }
}

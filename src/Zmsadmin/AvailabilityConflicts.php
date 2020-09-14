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
        $collection = new AvailabilityList();
        foreach($input['availabilityList'] as $item) {
            $entity = new Availability($item);
            $collection->addEntity($entity);
        }
        $selectedEntity = new Availability($input['selectedAvailability']);
        $data = ($input['selectedAvailability']) ? static::getAvailabilityData($collection, $selectedEntity) : [];
        return \BO\Slim\Render::withJson(
            $response,
            $data
        );
    }

    protected static function getAvailabilityData($collection, $selectedEntity)
    {
        $scope = new \BO\Zmsentities\Scope($selectedEntity->scope);
        $conflictList = $collection
            ->getConflicts($selectedEntity->getStartDateTime(), $selectedEntity->getEndDateTime());
        return [
            'conflictList' => $conflictList->toConflictListByDay(),
            'selectedAvailability' => $selectedEntity
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

<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Availability as Query;
use \BO\Zmsentities\Collection\AvailabilityList as Collection;

/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilityListByScope extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $startDateFormatted = Validator::param('startDate')->isString()->getValue();
        $endDateFormatted = Validator::param('endDate')->isString()->getValue();
        $getOpeningTimes = Validator::param('getOpeningTimes')->isNumber()->setDefault(0)->getValue();
        $startDate = ($startDateFormatted) ? new \BO\Zmsentities\Helper\DateTime($startDateFormatted) : null;
        $endDate = ($endDateFormatted) ? new \BO\Zmsentities\Helper\DateTime($endDateFormatted) : null;
        $accessRight = ($getOpeningTimes) ? 'basic' : 'availability';

        $scope = (new \BO\Zmsdb\Scope)->readEntity($args['id'], $resolveReferences - 1);
        $this->testScope($scope);

        (new Helper\User($request, 2))->checkRights(
            $accessRight,
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );
    
        $message = Response\Message::create($request);
        $message->meta->reducedData = ('basic' == $accessRight) ? true : false;
        $message->data = $this->getAvailabilityList($scope, $startDate, $endDate, $accessRight);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }

    protected function getAvailabilityList($scope, $startDate, $endDate, $accessRight)
    {
        $availabilityList = (new Query())->readList($scope->getId(), 0, $startDate, $endDate);
        $this->testAvailabilityList($availabilityList);

        if ($resolveReferences > 0) {
            $availabilityList = $availabilityList->withScope($scope);
        }
        if ('basic' == $accessRight) {
            $scope = (new \BO\Zmsdb\Scope)->readEntity(
                $scope->getId(),
                ($resolveReferences > 1) ? $resolveReferences : 1
            );
            $availabilityList = $availabilityList
                ->withScope($scope->withLessData(['dayoff']))
                ->withDateTimeInRange($startDate ? $startDate : \App::$now, $endDate ? $endDate : \App::$now)
                ->withLessData();
        }
        return $availabilityList;
    }

    protected function testScope($scope)
    {
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
    }

    protected function testAvailabilityList($availabilityList)
    {
        if (! $availabilityList->count()) {
            throw new Exception\Availability\AvailabilityNotFound();
        }
    }
}

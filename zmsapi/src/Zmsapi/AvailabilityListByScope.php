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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $startDateFormatted = Validator::param('startDate')->isString()->getValue();
        $endDateFormatted = Validator::param('endDate')->isString()->getValue();
        $graphQl = Validator::param('gql')->isString()->getValue();
        $startDate = ($startDateFormatted) ? new \BO\Zmsentities\Helper\DateTime($startDateFormatted) : null;
        $endDate = ($endDateFormatted) ? new \BO\Zmsentities\Helper\DateTime($endDateFormatted) : null;

        $scope = (new \BO\Zmsdb\Scope)->readEntity($args['id'], $resolveReferences);
        $this->testScope($scope);
        $this->testAccessRights($request, $scope);
        
        $message = Response\Message::create($request);
        $message->meta->reducedData = $graphQl ? true : false;
        $message->data = $this->getAvailabilityList($scope, $startDate, $endDate);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }

    protected function getAvailabilityList($scope, $startDate, $endDate)
    {
        $availabilityList = (new Query())->readList($scope->getId(), 0, $startDate, $endDate);
        $this->testAvailabilityList($availabilityList);
        return $availabilityList->withScope($scope);
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

    protected function testAccessRights($request, $scope)
    {
        try {
            (new Helper\User($request, 2))->checkRights(
                'availability',
                new \BO\Zmsentities\Useraccount\EntityAccess($scope)
            );
        } catch (\Exception $exception) {
            $token = $request->getHeader('X-Token');
            if (\App::SECURE_TOKEN != current($token)) {
                throw $exception;
            }
        }
    }
}

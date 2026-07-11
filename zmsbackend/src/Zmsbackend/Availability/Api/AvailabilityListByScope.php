<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Availability\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Availability\Service\Availability as Query;
use BO\Zmsentities\Collection\AvailabilityList as Collection;

/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilityListByScope extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
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

        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], $resolveReferences);
        $this->validateScope($scope);
        $this->validateAccessRights($request, $scope);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->meta->reducedData = $graphQl ? true : false;
        $message->data = $this->getAvailabilityList($scope, $startDate, $endDate);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }

    protected function getAvailabilityList($scope, $startDate, $endDate)
    {
        $availabilityList = (new Query())->readList($scope->getId(), 0, $startDate, $endDate);
        $this->validateAvailabilityList($availabilityList);
        return $availabilityList->withScope($scope);
    }

    protected function validateScope($scope)
    {
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
    }

    protected function validateAvailabilityList($availabilityList)
    {
        if (! $availabilityList->count()) {
            throw new \BO\Zmsbackend\Availability\Exception\AvailabilityNotFound();
        }
    }

    protected function validateAccessRights($request, $scope)
    {
        try {
            (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(
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

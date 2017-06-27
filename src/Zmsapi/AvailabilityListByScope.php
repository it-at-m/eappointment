<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Availability as Query;

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
        (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $reserveEntityIds = Validator::param('reserveEntityIds')->isNumber()->setDefault(0)->getValue();

        $scope = (new \BO\Zmsdb\Scope)->readEntity($args['id']);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $availabilities = (new Query())->readList($scope->id, $resolveReferences, $reserveEntityIds);
        if (0 == $availabilities->count()) {
            throw new Exception\Availability\AvailabilityNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $availabilities;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }
}

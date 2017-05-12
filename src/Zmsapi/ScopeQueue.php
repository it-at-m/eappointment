<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;
use \BO\Zmsentities\Helper\DateTime;

class ScopeQueue extends BaseController
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
        (new Helper\User($request))->checkRights('scope');
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = $query->readEntity($args['id'], $resolveReferences);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $scope = $query->readWithWorkstationCount($scope->id, $dateTime);
        $queueList = $query->readQueueListWithWaitingTime(
            $scope,
            $dateTime
        )->withPickupDestination($scope);

        $message = Response\Message::create($request);
        $message->data = $queueList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}

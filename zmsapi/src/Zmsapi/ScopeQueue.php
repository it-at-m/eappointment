<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope as Query;
use BO\Zmsentities\Helper\DateTime;

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
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? (new DateTime($selectedDate))->modify(\App::$now->format('H:i')) : \App::$now;

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = (new \BO\Zmsdb\Scope())->readWithWorkstationCount($args['id'], \App::$now, $resolveReferences);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $queueList = $query->readQueueListWithWaitingTime($scope, $dateTime, $resolveReferences);

        $message = Response\Message::create($request);
        if ((new Helper\User($request))->hasRights()) {
            (new Helper\User($request))->checkRights('basic');
        } else {
            $queueList = $queueList->withLessData();
            $message->meta->reducedData = true;
        }
        $message->data = $queueList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}

<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Scope\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope as Query;
use BO\Zmsentities\Helper\DateTime;

class ScopeQueue extends \BO\Zmsbackend\Api\BaseController
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
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? (new DateTime($selectedDate))->modify(\App::$now->format('H:i')) : \App::$now;

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readWithWorkstationCount($args['id'], \App::$now, $resolveReferences);
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
        $queueList = $query->readQueueListWithWaitingTime($scope, $dateTime, $resolveReferences);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $user = new \BO\Zmsbackend\Helper\User($request);
        if ($user->hasLogin()) {
            $user->checkPermissions();
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

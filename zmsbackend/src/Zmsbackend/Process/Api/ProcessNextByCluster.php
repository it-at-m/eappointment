<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Cluster\Service\Cluster as Query;
use BO\Zmsentities\Helper\DateTime;

class ProcessNextByCluster extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions('appointment');
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $exclude = Validator::param('exclude')->isString()->getValue();
        $allowClusterWideCall = Validator::param('allowClusterWideCall')->isBool()->setDefault(true)->getValue();
        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;
        $cluster = $query->readEntity($args['id']);
        if (! $cluster) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }

        (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(
            new \BO\Zmsentities\Useraccount\EntityAccess($cluster)
        );

        $queueList = $query->readQueueList($cluster->id, $dateTime, 1);
        if (! $allowClusterWideCall) {
            $queueList = $queueList
            ->toProcessList()
            ->withScopeId($workstation->getScope()->getId())
            ->toQueueList($dateTime);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = ProcessNextByScope::getProcess($queueList, $dateTime, $exclude);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}

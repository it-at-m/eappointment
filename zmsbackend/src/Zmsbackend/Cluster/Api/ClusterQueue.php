<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Cluster\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Cluster\Service\Cluster as Query;
use BO\Zmsentities\Helper\DateTime;

class ClusterQueue extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? (new DateTime($selectedDate))->modify(\App::$now->format('H:i')) : \App::$now;

        $cluster = $query->readEntity($args['id'], $resolveReferences);
        if (! $cluster) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }

        (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(
            'appointment',
            new \BO\Zmsentities\Useraccount\EntityAccess($cluster)
        );
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $queues = $query->readQueueList($cluster->id, $dateTime, $resolveReferences);
        $message->data = $queues;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}

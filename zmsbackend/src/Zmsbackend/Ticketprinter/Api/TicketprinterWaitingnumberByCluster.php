<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Ticketprinter\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Ticketprinter\Service\Ticketprinter as Query;
use BO\Zmsbackend\Cluster\Service\Cluster;
use BO\Zmsbackend\Process\Service\ProcessStatusQueued;

class TicketprinterWaitingnumberByCluster extends \BO\Zmsbackend\Api\BaseController
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
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity($args['id'], 0);
        if (! $cluster) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }

        $scope = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readScopeWithShortestWaitingTime($cluster->id, \App::$now);
        $process = \BO\Zmsbackend\Process\Service\ProcessStatusQueued::init()->writeNewFromTicketprinter($scope, \App::$now);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

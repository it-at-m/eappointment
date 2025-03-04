<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Workstation;
use BO\Zmsentities\Helper\DateTime;

class WorkstationListByCluster extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $cluster = (new \BO\Zmsdb\Cluster())->readEntity($args['id'], 0);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $workstationList = (new Workstation())->readLoggedInListByCluster($cluster->id, \App::$now, $resolveReferences);

        $message = Response\Message::create($request);
        $message->data = $workstationList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}

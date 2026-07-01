<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Organisation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Organisation\Service\Organisation as Query;
use BO\Zmsbackend\Cluster\Service\Cluster;

class OrganisationByCluster extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity($args['id']);
        if (! $cluster) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }
        $organisation = (new Query())->readByClusterId($cluster->id, $resolveReferences);
        if (! $organisation->hasId()) {
            throw new \BO\Zmsbackend\Organisation\Exception\OrganisationNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        if ((new \BO\Zmsbackend\Helper\User($request))->hasRights()) {
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('cluster');
        } else {
            $organisation = $organisation->withLessData();
            $message->meta->reducedData = true;
        }
        $message->data = $organisation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

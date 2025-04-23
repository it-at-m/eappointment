<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Organisation as Query;
use BO\Zmsdb\Cluster;

class OrganisationByCluster extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $cluster = (new Cluster())->readEntity($args['id']);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $organisation = (new Query())->readByClusterId($cluster->id, $resolveReferences);
        if (! $organisation->hasId()) {
            throw new Exception\Organisation\OrganisationNotFound();
        }

        $message = Response\Message::create($request);
        if ((new Helper\User($request))->hasRights()) {
            (new Helper\User($request))->checkRights('basic');
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

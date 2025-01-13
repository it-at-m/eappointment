<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Cluster as Query;

class ClusterCalldisplayImageDataUpdate extends BaseController
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
        (new Helper\User($request))->checkRights('cluster');
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $cluster = (new Query())->readEntity($args['id']);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }

        $input = Validator::input()->isJson()->getValue();
        $mimepart = new \BO\Zmsentities\Mimepart($input);

        $message = Response\Message::create($request);
        $message->data = (new Query())->writeImageData($cluster->id, $mimepart);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}

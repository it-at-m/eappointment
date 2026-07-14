<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Cluster\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Cluster\Service\Cluster as Query;

class ClusterCalldisplayImageDataUpdate extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('calldisplay');
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $cluster = (new Query())->readEntity($args['id']);
        if (! $cluster) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }

        $input = Validator::input()->isJson()->getValue();
        $mimepart = new \BO\Zmsentities\Mimepart($input);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new Query())->writeImageData($cluster->id, $mimepart);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}

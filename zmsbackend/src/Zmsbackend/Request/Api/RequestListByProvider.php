<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Request\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Request\Service\Request;

class RequestListByProvider extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $provider = (new \BO\Zmsbackend\Provider\Service\Provider())->readEntity($args['source'], $args['id'], $resolveReferences);
        if (! $provider->hasId()) {
            throw new \BO\Zmsbackend\Provider\Exception\ProviderNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Request\Service\Request())->readListByProvider($args['source'], $provider->id, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}

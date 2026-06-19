<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Provider\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Provider\Service\Provider;

class ProviderGet extends \BO\Zmsbackend\Api\BaseController
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
        $message->data = $provider;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

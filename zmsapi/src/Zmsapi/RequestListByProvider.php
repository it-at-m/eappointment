<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Request;

class RequestListByProvider extends BaseController
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
        $provider = (new \BO\Zmsdb\Provider())->readEntity($args['source'], $args['id'], $resolveReferences);
        if (! $provider->hasId()) {
            throw new Exception\Provider\ProviderNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = (new Request())->readListByProvider($args['source'], $provider->id, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}

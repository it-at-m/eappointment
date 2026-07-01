<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Apikey\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Apikey\Service\Apikey as Query;

class ApikeyGet extends \BO\Zmsbackend\Api\BaseController
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
        $entity = (new Query())->readEntity($args['key']);
        if (! $entity->hasId()) {
            throw new \BO\Zmsbackend\Apikey\Exception\ApiKeyNotFound();
        }
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $entity;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

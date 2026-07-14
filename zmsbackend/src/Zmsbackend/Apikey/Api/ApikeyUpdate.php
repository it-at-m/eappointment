<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Apikey\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Apikey\Service\Apikey as Query;

class ApikeyUpdate extends \BO\Zmsbackend\Api\BaseController
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
        $validator = $request->getAttribute('validator');
        $clientKey = $validator->getParameter('clientkey')->isString()->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Apikey($input);

        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $apiKey = (new Query())->readEntity($entity->key);
        if ($clientKey) {
            $apiClient = (new \BO\Zmsbackend\Apikey\Service\Apiclient())->readEntity($clientKey);
            if (!$apiClient || !isset($apiClient->accesslevel) || $apiClient->accesslevel == 'blocked') {
                throw new \BO\Zmsbackend\Process\Exception\ApiclientInvalid();
            }
            $apiKey->setApiclient($apiClient);
        }
        if (! $apiKey->hasId()) {
            $entity = (new Query())->writeEntity($entity);
        } else {
            $entity = (new Query())->updateEntity($entity->key, $entity);
        }
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $entity;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

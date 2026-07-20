<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Availability\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Availability\Service\Availability as AvailabilityRepository;
use BO\Zmsbackend\Helper\CalculateSlots as CalculateSlotsHelper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsentities\Availability as Entity;
use App;

class AvailabilityDelete extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('availability');
        $repository = new AvailabilityRepository();
        $entity = $repository->readEntity($args['id'], 2);

        if ($entity->scope && $entity->hasId() && $repository->deleteEntity($entity->getId())) {
            (new CalculateSlotsHelper(\App::DEBUG))->writePostProcessingByScope($entity->scope, \App::$now);
            App::$log->info('Deleted availability', [
                'id' => $entity->getId(),
                'scope_id' => $entity->scope['id'],
                'operation' => 'delete'
            ]);
        } else {
            $entity = new Entity(['id' => $args['id']]);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $entity;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }
}

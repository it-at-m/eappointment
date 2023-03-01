<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;

use BO\Zmsdb\Availability as AvailabilityRepository;
use BO\Zmsdb\Helper\CalculateSlots as CalculateSlotsHelper;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use BO\Zmsentities\Availability as Entity;

class AvailabilityDelete extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        (new Helper\User($request))->checkRights();
        $repository = new AvailabilityRepository();
        $entity = $repository->readEntity($args['id'], 2);

        if (! $entity) {
            $entity = new Entity(['id' => $entityId]);
        }
        if ($repository->deleteEntity($entity->getId())) {
            (new CalculateSlotsHelper(\App::DEBUG))->writePostProcessingByScope($entity->scope, \App::$now);
        }

        $message = Response\Message::create($request);
        $message->data = $entity;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }
}

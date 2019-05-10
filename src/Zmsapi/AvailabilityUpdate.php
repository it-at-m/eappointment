<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Availability as Query;

class AvailabilityUpdate extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Availability($input);
        $availability = (new Query())->readEntity($args['id'], $resolveReferences);
        if (! $availability->hasId()) {
            throw new Exception\Availability\AvailabilityNotFound();
        }
        $updatedEntity = (new Query())->updateEntity($args['id'], $entity, 2);
        (new \BO\Zmsdb\Slot)->writeByAvailability($updatedEntity, \App::$now);
        (new \BO\Zmsdb\Helper\CalculateSlots(\App::DEBUG))
            ->writePostProcessingByScope($updatedEntity->scope, \App::$now);

        $message = Response\Message::create($request);
        $message->data = $updatedEntity;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

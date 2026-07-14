<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Availability\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Availability\Service\Availability as Query;

class AvailabilityGet extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $availability = (new Query())->readEntity($args['id'], $resolveReferences);
        if (! $availability->hasId()) {
            throw new \BO\Zmsbackend\Availability\Exception\AvailabilityNotFound();
        }
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $availability;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

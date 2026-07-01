<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsbackend\Owner\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Owner\Service\Owner as Query;

/**
 * Delete an owner by Id
 */
class OwnerDelete extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('jurisdiction');
        $query = new Query();
        $owner = $query->readEntity($args['id'], 2);
        if (! $owner->hasId()) {
            throw new \BO\Zmsbackend\Owner\Exception\OwnerNotFound();
        }(new \BO\Zmsbackend\Helper\User($request, 2))->checkRights(
            new \BO\Zmsentities\Useraccount\EntityAccess($owner)
        );
        $query->deleteEntity($owner->id);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $owner;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

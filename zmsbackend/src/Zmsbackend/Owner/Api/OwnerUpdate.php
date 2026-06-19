<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Owner\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Owner\Service\Owner as Query;

class OwnerUpdate extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('jurisdiction');
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Owner($input);
        $entity->testValid();
        $owner = (new Query())->readEntity($args['id']);
        if (! $owner->hasId()) {
            throw new \BO\Zmsbackend\Owner\Exception\OwnerNotFound();
        }(new \BO\Zmsbackend\Helper\User($request, 2))->checkRights(
            new \BO\Zmsentities\Useraccount\EntityAccess($owner)
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new Query())->updateEntity($owner->id, $entity);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

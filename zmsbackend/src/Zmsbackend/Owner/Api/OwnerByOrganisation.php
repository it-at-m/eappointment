<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Owner\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Owner\Service\Owner;

class OwnerByOrganisation extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();

        $owner = (new \BO\Zmsbackend\Owner\Service\Owner())->readByOrganisationId(
            $args['id'],
            ($resolveReferences > 0) ? $resolveReferences : 1
        );
        if (! $owner || ! $owner->organisations->getEntity($args['id'])) {
            throw new \BO\Zmsbackend\Organisation\Exception\OrganisationNotFound();
        }


        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $owner->withResolveLevel($resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

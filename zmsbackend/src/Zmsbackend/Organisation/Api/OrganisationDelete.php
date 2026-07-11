<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsbackend\Organisation\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Organisation\Service\Organisation as Query;

/**
 * Delete an organisation by Id
 */
class OrganisationDelete extends \BO\Zmsbackend\Api\BaseController
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
        $query = new Query();
        $organisation = $query->readEntity($args['id'], 1);
        if (! $organisation) {
            throw new \BO\Zmsbackend\Organisation\Exception\OrganisationNotFound();
        }(new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(

            'organisation',
            new \BO\Zmsentities\Useraccount\EntityAccess($organisation)
        );
        $query->deleteEntity($organisation->id);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $organisation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

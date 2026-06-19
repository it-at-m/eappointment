<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Organisation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Organisation\Service\Organisation as Query;

class OrganisationUpdate extends \BO\Zmsbackend\Api\BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Organisation($input);
        $organisation = (new Query())->readEntity($args['id'], 1);
        if (! $organisation) {
            throw new \BO\Zmsbackend\Organisation\Exception\OrganisationNotFound();
        }(new \BO\Zmsbackend\Helper\User($request, 2))->checkRights(
            'organisation',
            new \BO\Zmsentities\Useraccount\EntityAccess($organisation)
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new Query())->updateEntity($organisation->id, $entity);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

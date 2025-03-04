<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Organisation as Query;

class OrganisationUpdate extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Organisation($input);
        $organisation = (new Query())->readEntity($args['id'], 1);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }(new Helper\User($request, 2))->checkRights(
            'organisation',
            new \BO\Zmsentities\Useraccount\EntityAccess($organisation)
        );

        $message = Response\Message::create($request);
        $message->data = (new Query())->updateEntity($organisation->id, $entity);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

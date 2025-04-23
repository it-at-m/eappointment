<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Organisation as Query;

class OwnerAddOrganisation extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $owner = (new \BO\Zmsdb\Owner())->readEntity($args['id'], 2);
        (new Helper\User($request, 2))->checkRights(
            new \BO\Zmsentities\Useraccount\EntityAccess($owner)
        );
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $organisation = new \BO\Zmsentities\Organisation($input);
        $organisation->testValid();

        $message = Response\Message::create($request);
        $message->data = (new Query())->writeEntity($organisation, $args['id']);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

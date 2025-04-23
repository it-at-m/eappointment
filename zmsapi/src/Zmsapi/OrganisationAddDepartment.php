<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Department as Query;

class OrganisationAddDepartment extends BaseController
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
        $organisation = (new \BO\Zmsdb\Organisation())->readEntity($args['id'], 1);
        (new Helper\User($request, 2))->checkRights(
            'department',
            new \BO\Zmsentities\Useraccount\EntityAccess($organisation)
        );
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $department = new \BO\Zmsentities\Department($input);
        $department->testValid();

        $message = Response\Message::create($request);
        $message->data = (new Query())->writeEntity($department, $args['id']);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

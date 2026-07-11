<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Organisation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Department\Service\Department as Query;

class OrganisationAddDepartment extends \BO\Zmsbackend\Api\BaseController
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
        $user = new \BO\Zmsbackend\Helper\User($request, 2);
        $user->checkPermissions('department');
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $organisation = (new \BO\Zmsbackend\Organisation\Service\Organisation())->readEntity($args['id'], 1);
        $user->checkPermissions(

            'department',
            new \BO\Zmsentities\Useraccount\EntityAccess($organisation)
        );
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $department = new \BO\Zmsentities\Department($input);
        $department->testValid();

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new Query())->writeEntity($department, $args['id']);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

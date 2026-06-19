<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Department\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Department\Service\Department as Query;

class DepartmentUpdate extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 2));
        $department =  $workstation::checkDepartment($args['id']);
        $department->addData($input)->testValid('de_DE', 1);
        $workstation->checkRights(
            'department',
            new \BO\Zmsentities\Useraccount\EntityAccess($department)
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new Query())->updateEntity($department->id, $department);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

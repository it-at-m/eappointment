<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Department as Query;

class DepartmentUpdate extends BaseController
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
        $workstation = (new Helper\User($request, 2));
        $department =  $workstation::checkDepartment($args['id']);
        $department->addData($input)->testValid('de_DE', 1);
        $workstation->checkRights(
            'department',
            new \BO\Zmsentities\Useraccount\EntityAccess($department)
        );

        $message = Response\Message::create($request);
        $message->data = (new Query())->updateEntity($department->id, $department);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

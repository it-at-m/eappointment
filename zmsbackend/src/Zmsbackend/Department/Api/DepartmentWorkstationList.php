<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Department\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Workstation\Service\Workstation;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DepartmentWorkstationList extends \BO\Zmsbackend\Api\BaseController
{
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        (new \BO\Zmsbackend\Helper\User($request))->checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = \BO\Zmsbackend\Helper\User::checkDepartment($args['id']);

        $workstationList = (new \BO\Zmsbackend\Workstation\Service\Workstation())->readCollectionByDepartmentId($department->id, $resolveReferences);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $workstationList;

        $response = Render::withLastModified($response, time(), '0');

        return Render::withJson($response, $message, 200);
    }
}

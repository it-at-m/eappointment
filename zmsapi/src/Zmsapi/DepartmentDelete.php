<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Department as Query;

class DepartmentDelete extends BaseController
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
        $query = new Query();
        (new Helper\User($request, 2))->checkRights('department');
        $department = Helper\User::checkDepartment($args['id']);
        $query->deleteEntity($department->id);

        $message = Response\Message::create($request);
        $message->data = $department;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

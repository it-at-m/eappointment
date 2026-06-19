<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsbackend\Department\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Department\Service\Department as Query;

class DepartmentDelete extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request, 2))->checkRights('department');
        $department = \BO\Zmsbackend\Helper\User::checkDepartment($args['id']);
        $query->deleteEntity($department->id);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $department;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

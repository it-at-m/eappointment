<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Department as Query;

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
        (new Helper\User($request))->checkRights('department');
        $query = new Query();
        $department = $query->readEntity($args['id']);
        if (! $department->hasId()) {
            throw new Exception\Department\DepartmentNotFound();
        }
        $query->deleteEntity($department->id);

        $message = Response\Message::create($request);
        $message->data = $department;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

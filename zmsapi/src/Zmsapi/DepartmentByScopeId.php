<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

class DepartmentByScopeId extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = (new \BO\Zmsdb\Department())->readByScopeId($args['id'], $resolveReferences);
        if (! $department) {
            throw new Exception\Department\DepartmentNotFound();
        }

        $message = Response\Message::create($request);
        if ((new Helper\User($request))->hasRights()) {
            (new Helper\User($request))->checkRights('basic');
        } else {
            $department = $department->withLessData();
            $message->meta->reducedData = true;
        }
        $message->data = $department;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

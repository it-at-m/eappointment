<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Department\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope as Query;

class DepartmentByScopeId extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = (new \BO\Zmsbackend\Department\Service\Department())->readByScopeId($args['id'], $resolveReferences);
        if (! $department) {
            throw new \BO\Zmsbackend\Department\Exception\DepartmentNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $user = new \BO\Zmsbackend\Helper\User($request);
        if ($user->hasLogin()) {
            $user->checkPermissions();

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

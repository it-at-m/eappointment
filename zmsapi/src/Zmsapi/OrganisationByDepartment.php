<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Organisation;

class OrganisationByDepartment extends BaseController
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
        (new Helper\User($request))->checkRights('basic');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();

        $organisation = (new Organisation())->readByDepartmentId(
            $args['id'],
            ($resolveReferences > 0) ? $resolveReferences : 1
        );
        if (! $organisation || ! $organisation->departments->getEntity($args['id'])) {
            throw new Exception\Department\DepartmentNotFound();
        }


        $message = Response\Message::create($request);
        $message->data = $organisation->withResolveLevel($resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

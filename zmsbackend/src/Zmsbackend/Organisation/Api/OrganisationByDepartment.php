<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Organisation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Organisation\Service\Organisation;

class OrganisationByDepartment extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();

        $organisation = (new \BO\Zmsbackend\Organisation\Service\Organisation())->readByDepartmentId(
            $args['id'],
            ($resolveReferences > 0) ? $resolveReferences : 1
        );
        if (! $organisation || ! $organisation->departments->getEntity($args['id'])) {
            throw new \BO\Zmsbackend\Department\Exception\DepartmentNotFound();
        }


        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $organisation->withResolveLevel($resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

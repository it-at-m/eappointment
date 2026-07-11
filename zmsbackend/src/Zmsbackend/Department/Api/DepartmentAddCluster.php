<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Department\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Cluster\Service\Cluster as Query;

class DepartmentAddCluster extends \BO\Zmsbackend\Api\BaseController
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
        $department = (new \BO\Zmsbackend\Department\Service\Department())->readEntity($args['id'], 1);
        (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(
            'department',
            new \BO\Zmsentities\Useraccount\EntityAccess($department)
        );
        \BO\Zmsbackend\Helper\User::checkDepartment($args['id']);
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $cluster = new \BO\Zmsentities\Cluster($input);
        $cluster->testValid();

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new Query())->writeEntity($cluster);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

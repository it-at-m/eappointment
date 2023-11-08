<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Cluster as Query;

class DepartmentAddCluster extends BaseController
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
        $department = (new \BO\Zmsdb\Department)->readEntity($args['id'], 1);
        (new Helper\User($request, 2))->checkRights(
            'department',
            new \BO\Zmsentities\Useraccount\EntityAccess($department)
        );
        Helper\User::checkDepartment($args['id']);
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $cluster = new \BO\Zmsentities\Cluster($input);
        $cluster->testValid();

        $message = Response\Message::create($request);
        $message->data = (new Query())->writeEntity($cluster);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

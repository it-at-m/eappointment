<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

class DepartmentAddScope extends BaseController
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
        $scope = new \BO\Zmsentities\Scope($input);
        $scope->testValid();

        $message = Response\Message::create($request);
        $message->data = (new Query())->writeEntity($scope, $args['id']);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

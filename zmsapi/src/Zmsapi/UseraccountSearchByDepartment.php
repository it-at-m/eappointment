<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Useraccount;
use \BO\Zmsentities\Collection\UseraccountList as Collection;

class UseraccountSearchByDepartment extends BaseController
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
        $workstation = (new Helper\User($request, 2))->checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = Helper\User::checkDepartment($args['id']);
        $parameters = $request->getParams();

        $useraccountList = (new Useraccount)->readSearchByDepartmentId($department->id, $parameters, $resolveReferences);
        $useraccountList = $useraccountList->withAccessByWorkstation($workstation);
        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);

        return $response;
    }

}

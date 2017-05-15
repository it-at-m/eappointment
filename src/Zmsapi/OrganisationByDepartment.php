<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Organisation;
use \BO\Zmsdb\Department;

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
        $workstation = (new Helper\User($request))->checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $department = Helper\User::checkDepartment($args['id']);
        if (! $department->hasId()) {
            throw new Exception\Department\DepartmentNotFound();
        }

        $organisation = (new Organisation())->readByDepartmentId($department->id, $resolveReferences);
        $organisation->departments = $organisation->getDepartmentList()->withAccess($workstation->getUseraccount());

        $message = Response\Message::create($request);
        $message->data = $organisation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

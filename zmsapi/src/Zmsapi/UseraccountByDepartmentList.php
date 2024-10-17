<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Useraccount as Query;
use \BO\Zmsentities\Collection\UseraccountList as Collection;
use BO\Zmsentities\Useraccount;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DepartmentUseraccountList extends BaseController
{
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $workstation = (new Helper\User($request, 1))->checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = Helper\User::checkDepartment($args['id']);

        /** @var Useraccount $userAccount */
        $userAccountList = (new Query)->readCollectionByDepartmentId($department->id, $resolveReferences);
        foreach ($userAccountList as $userAccount) {
            if ($resolveReferences < 1 && !$userAccount->getDepartmentById($department->id)) {
                $userAccount->getDepartmentList()->addEntity($department);
            }
        }

        $message = Response\Message::create($request);
        $message->data = $userAccountList;

        $response = Render::withLastModified($response, time(), '0');

        return Render::withJson($response, $message, 200);
    }
}

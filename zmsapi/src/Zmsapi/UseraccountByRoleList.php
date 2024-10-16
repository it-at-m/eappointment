<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Mellon\Validator;
use \BO\Slim\Render;
use \BO\Zmsdb\Useraccount;
use \BO\Zmsentities\Collection\UseraccountList as Collection;

class UseraccountByRoleList extends BaseController
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
        $roleLevel = $args['level'];
        $workstation = (new Helper\User($request, 2))->checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();

        $useraccountList = new Collection();
        $useraccountList = (new Useraccount)->readListRole($roleLevel, $resolveReferences)->withLessData();
        $useraccountList = $useraccountList->withAccessByWorkstation($workstation);

        if (! $useraccountList or count($useraccountList) === 0) {
            throw new \BO\Zmsapi\Exception\Useraccount\UserRoleNotFound();
        }
        
        $validUserAccounts = [];
        foreach ($useraccountList as $useraccount) {
            try {
                Helper\User::testWorkstationAccessRights($useraccount);
                $validUserAccounts[] = $useraccount;
            } catch (\BO\Zmsentities\Exception\UserAccountAccessRightsFailed $e) {
                continue;
            }
        }
        $useraccountList = $validUserAccounts;
        
        $message = Response\Message::create($request);
        $message->data = $useraccountList;
        
        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);

        return $response;
    }
}

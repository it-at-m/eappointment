<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsdb\Useraccount;
use BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountList extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request, 1))->checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();

        /** @var Useraccount $useraccount */
        $useraccountList = new Collection();
        // Load with departments needed for access check (resolveReferences=1)
        // TODO: Optimize to batch load departments to avoid N+1 queries
        $useraccountList = (new Useraccount())->readList(1);

        $validUserAccounts = [];
        foreach ($useraccountList as $useraccount) {
            try {
                Helper\User::testWorkstationAccessRights($useraccount);
                // Remove departments after access check since withLessData() removes them anyway
                $validUserAccounts[] = $useraccount->withLessData();
            } catch (\BO\Zmsentities\Exception\UserAccountAccessRightsFailed $e) {
                continue;
            }
        }
        $useraccountList = $validUserAccounts;

        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}

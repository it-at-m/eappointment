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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountSearch extends BaseController
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
        $parameters = $request->getParams();

        /** @var Useraccount $useraccount */
        $useraccountList = new Collection();
        $useraccountList = (new Useraccount)->readSearch($parameters, $resolveReferences)->withLessData();

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
        return Render::withJson($response, $message, 200);
    }

}

<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Useraccount;
use BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountListByRole extends BaseController
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
        $roleLevel = $args['level'];
        $workstation = (new Helper\User($request, 1))->checkRights('useraccount');

        $useraccountList = (new Useraccount())->readListRole($roleLevel, 0, $workstation);

        if (! $useraccountList or count($useraccountList) === 0) {
            throw new \BO\Zmsapi\Exception\Useraccount\UserRoleNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}

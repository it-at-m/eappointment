<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Useraccount;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountListByRole extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $roleName = $args['roleName'];
        $workstation = (new Helper\User($request, 1))->checkPermissions('useraccount');

        $useraccountList = (new Useraccount())->readListRole($roleName, 0, $workstation);

        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}

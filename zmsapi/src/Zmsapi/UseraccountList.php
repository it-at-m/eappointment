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
        $helper = new Helper\User($request, 2);
        $helper->checkRights('useraccount');
        $parameters = $request->getParams();

        $workstation = Helper\User::$workstation;
        $useraccountList = (new Useraccount())->readSearch($parameters, 1, $workstation);

        $message = Response\Message::create($request);
        $message->data = $useraccountList->withLessData();

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}

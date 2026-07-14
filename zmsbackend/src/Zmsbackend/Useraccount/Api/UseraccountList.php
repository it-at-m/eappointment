<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Useraccount\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Useraccount\Service\Useraccount;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountList extends \BO\Zmsbackend\Api\BaseController
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
        $helper = new \BO\Zmsbackend\Helper\User($request, 1);
        $helper->checkPermissions('useraccount');
        $parameters = $request->getParams();

        $workstation = \BO\Zmsbackend\Helper\User::$workstation;
        $useraccountList = (new \BO\Zmsbackend\Useraccount\Service\Useraccount())->readSearch($parameters, 0, $workstation);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}

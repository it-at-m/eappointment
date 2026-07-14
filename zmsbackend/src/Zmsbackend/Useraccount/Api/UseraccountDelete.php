<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Useraccount\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Useraccount\Service\Useraccount;

class UseraccountDelete extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();

        (new \BO\Zmsbackend\Helper\User($request, $resolveReferences))->checkPermissions('useraccount');
        $useraccountModel = new \BO\Zmsbackend\Useraccount\Service\Useraccount();
        $useraccount = $useraccountModel->readEntity($args['loginname'], $resolveReferences);
        if (! $useraccount || ! $useraccount->hasId()) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound();
        }

        \BO\Zmsbackend\Helper\User::testWorkstationAccessRights($useraccount);

        if (! $useraccountModel->deleteEntity($useraccount->getId())) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $useraccount;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

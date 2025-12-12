<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Useraccount;

class UseraccountDelete extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();

        (new Helper\User($request, $resolveReferences))->checkRights('useraccount');
        $useraccountModel = new Useraccount();
        $useraccount = $useraccountModel->readEntity($args['loginname'], $resolveReferences);
        if (! $useraccount || ! $useraccount->hasId()) {
            throw new Exception\Useraccount\UseraccountNotFound();
        }

        Helper\User::testWorkstationAccessRights($useraccount);

        if (! $useraccountModel->deleteEntity($useraccount->getId())) {
            throw new Exception\Useraccount\UseraccountNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $useraccount;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

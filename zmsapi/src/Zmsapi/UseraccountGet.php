<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsdb\Useraccount;

class UseraccountGet extends BaseController
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

        (new Helper\User($request, $resolveReferences))->checkPermissions('useraccount');

        $useraccount = (new Useraccount())->readEntity($args['loginname'], $resolveReferences);
        if (! $useraccount || ! $useraccount->hasId()) {
            throw new Exception\Useraccount\UseraccountNotFound();
        }

        try {
            Helper\User::testWorkstationAccessRights($useraccount);
        } catch (\BO\Zmsentities\Exception\UserAccountAccessRightsFailed $e) {
            throw new Exception\Useraccount\UseraccountNotFound();
        }
        $message = Response\Message::create($request);
        $message->data = $useraccount;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\UserAccount;

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
        (new Helper\User($request))->checkRights('useraccount');
        $userAccount = (new Useraccount)->readEntity($args['loginname']);
        if (! $userAccount->hasId() || ! (new Useraccount)->deleteEntity($userAccount->id)) {
            throw new Exception\Useraccount\UseraccountNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $userAccount;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

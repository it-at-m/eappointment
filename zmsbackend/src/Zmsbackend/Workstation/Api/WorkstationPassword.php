<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Useraccount\Service\Useraccount as Query;

class WorkstationPassword extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 3))->checkPermissions();
        $useraccount = $workstation->getUseraccount();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Useraccount($input);
        $entity->testValid();
        if (isset($input['email'])) {
            $useraccount->email = $input['email'];
        }
        \BO\Zmsbackend\Helper\UserAuth::testPasswordMatching($useraccount, $input['password']);
        if (isset($input['changePassword'])) {
            $useraccount->password = $useraccount->getHash(reset($input['changePassword']));
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new Query())->writeUpdatedEntity($useraccount->getId(), $useraccount);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

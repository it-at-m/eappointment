<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Useraccount as Query;

class WorkstationPassword extends BaseController
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
        $workstation = (new Helper\User($request, 3))->checkRights();
        $useraccount = $workstation->getUseraccount();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Useraccount($input);
        $entity->testValid();
        if (isset($input['email'])) {
            $useraccount->email = $input['email'];
        }
        Helper\UserAuth::testPasswordMatching($useraccount, $input['password']);
        if (isset($input['changePassword'])) {
            $useraccount->password = $useraccount->getHash(reset($input['changePassword']));
        }

        $message = Response\Message::create($request);
        $message->data = (new Query)->writeUpdatedEntity($useraccount->getId(), $useraccount);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

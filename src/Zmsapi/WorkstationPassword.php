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
        $workstation = (new Helper\User($request))->checkRights();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $useraccount = new \BO\Zmsentities\Useraccount($input);
        $useraccount->testValid();
        Helper\UserAuth::testUseraccountExists($workstation->getUseraccount()->id, $useraccount->password);
        if ($useraccount->changePassword) {
            $useraccount->password = reset($useraccount->changePassword);
        }

        $message = Response\Message::create($request);
        $message->data = (new Query)->updateEntity($workstation->getUseraccount()->id, $useraccount);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

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
        $entity = new \BO\Zmsentities\Useraccount($input);
        $entity->testValid();
        Helper\UserAuth::testUseraccountExists($entity->getId());
        $useraccount = Helper\UserAuth::getVerifiedUseraccount($entity);
        Helper\UserAuth::testPasswordMatching($useraccount, $entity->password);        
        if (isset($entity->changePassword)) {
            $useraccount->password = $useraccount->getHash(reset($entity->changePassword));
        }

        $message = Response\Message::create($request);
        $message->data = (new Query)->updateEntity($workstation->getUseraccount()->getId(), $useraccount);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Useraccount;

class UseraccountAdd extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        if (0 == count($input)) {
            throw new Exception\Useraccount\UseraccountInvalidInput();
        }
        $entity = new \BO\Zmsentities\Useraccount($input);
        $entity->testValid();

        if ((new Useraccount)->readIsUserExisting($entity->id)) {
            throw new Exception\Useraccount\UseraccountAlreadyExists();
        }

        $message = Response\Message::create($request);
        $message->data = (new Useraccount)->writeEntity($entity, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}

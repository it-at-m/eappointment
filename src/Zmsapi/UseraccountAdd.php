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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        (new Helper\User($request, $resolveReferences))->checkRights('useraccount');
        $input = Validator::input()->isJson()->assertValid()->getValue();

        $entity = new \BO\Zmsentities\Useraccount($input);
        $this->testEntity($entity, $input);
        Helper\User::testWorkstationAccessRights($entity);
        $entity->password = $entity->getHash($entity->password);

        $message = Response\Message::create($request);
        $message->data = (new Useraccount)->writeEntity($entity, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testEntity($entity, $input)
    {
        if (0 == count($input)) {
            throw new Exception\Useraccount\UseraccountInvalidInput();
        }
        try {
            $entity->testValid('de_DE', 1);
        } catch (\Exception $exception) {
            $exception->data['input'] = $input;
            throw $exception;
        }

        if ((new Useraccount)->readIsUserExisting($entity->id)) {
            throw new Exception\Useraccount\UseraccountAlreadyExists();
        }
    }
}

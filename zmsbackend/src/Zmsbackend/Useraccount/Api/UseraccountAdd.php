<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Useraccount\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Useraccount\Service\Useraccount;

class UseraccountAdd extends \BO\Zmsbackend\Api\BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();

        $entity = new \BO\Zmsentities\Useraccount($input);
        $this->testEntity($entity, $input);
        \BO\Zmsbackend\Helper\User::testWorkstationAssignedRoles($entity);
        \BO\Zmsbackend\Helper\User::testWorkstationAccessRights($entity);
        $entity->password = $entity->getHash($entity->password);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Useraccount\Service\Useraccount())->writeEntity($entity, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testEntity($entity, $input)
    {
        if (0 == count($input)) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountInvalidInput();
        }
        try {
            $entity->testValid('de_DE', 1);
        } catch (\Exception $exception) {
            $exception->data['input'] = $input;
            throw $exception;
        }

        if ((new \BO\Zmsbackend\Useraccount\Service\Useraccount())->readIsUserExisting($entity->id)) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountAlreadyExists();
        }
    }
}

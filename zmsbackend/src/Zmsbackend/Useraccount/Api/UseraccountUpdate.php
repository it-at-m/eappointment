<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Useraccount\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Useraccount\Service\Useraccount;

/**
 * @SuppressWarnings(Coupling)
 * @return \Psr\Http\Message\ResponseInterface
 */
class UseraccountUpdate extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions('useraccount');

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Useraccount($input);
        if (! (new \BO\Zmsbackend\Useraccount\Service\Useraccount())->readIsUserExisting($args['loginname'])) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound();
        }

        $this->testEntity($entity, $input, $args);
        \BO\Zmsbackend\Helper\User::testWorkstationAssignedRoles($entity);

        if (isset($entity->changePassword)) {
            $entity->password = $entity->getHash(reset($entity->changePassword));
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Useraccount\Service\Useraccount())->writeUpdatedEntity($args['loginname'], $entity, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testEntity($entity, $input, $args)
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

        if ($args['loginname'] != $entity->id && (new \BO\Zmsbackend\Useraccount\Service\Useraccount())->readIsUserExisting($entity->id)) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountAlreadyExists();
        }

        \BO\Zmsbackend\Helper\UserAuth::testUseraccountExists($args['loginname']);
        \BO\Zmsbackend\Helper\User::testWorkstationAccessRights($entity);
        \BO\Zmsbackend\Helper\User::testWorkstationAssignedRights($entity);
    }
}

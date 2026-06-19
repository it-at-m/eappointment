<?php

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Workstation\Service\Workstation;
use BO\Zmsbackend\Useraccount\Service\Useraccount;
use BO\Zmsentities\Useraccount as UseraccountEntity;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationOAuth extends \BO\Zmsbackend\Api\BaseController
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
        $validator = $request->getAttribute('validator');
        $resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $state  = $validator->getParameter('state')->isString()->isSmallerThan(40)->isBiggerThan(30)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = (new UseraccountEntity())->createFromOpenIdData($input);
        $entity->testValid();

        if (null === $state || $request->getHeaderLine('X-Authkey') !== $state) {
            throw new \BO\Zmsbackend\Workstation\Exception\WorkstationAuthFailed();
        }
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        if ((new \BO\Zmsbackend\Useraccount\Service\Useraccount())->readIsUserExisting($entity->getId())) {
            $workstation = $this->getLoggedInWorkstationByOidc($request, $entity, $resolveReferences);
        } else {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound();
        }
        \BO\Zmsbackend\Connection\Select::writeCommit();

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function getLoggedInWorkstationByOidc($request, $entity, $resolveReferences)
    {
        \BO\Zmsbackend\Helper\UserAuth::testUseraccountExists($entity->getId());

        $workstation = (new \BO\Zmsbackend\Helper\User($request, $resolveReferences))->readWorkstation();
        \BO\Zmsbackend\Helper\User::testWorkstationIsOveraged($workstation);

        WorkstationLogin::testLoginHash($workstation);
        $workstation = (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeEntityLoginByOidc(
            $entity->id,
            $request->getHeaderLine('X-Authkey'),
            \App::getNow(),
            (new \DateTime())->setTimestamp(time() + \App::SESSION_DURATION),
            $resolveReferences
        );
        return $workstation;
    }
}

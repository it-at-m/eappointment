<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Workstation;
use BO\Zmsdb\Useraccount;
use BO\Zmsentities\Useraccount as UseraccountEntity;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationOAuth extends BaseController
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
        $validator = $request->getAttribute('validator');
        $resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $state  = $validator->getParameter('state')->isString()->isSmallerThan(40)->isBiggerThan(30)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = (new UseraccountEntity())->createFromOpenIdData($input);
        $entity->testValid();

        $log = \App::getContainer()->get('log');
        $log->debug("OIDC AUTH DEBUG", [
            'state' => $state,
            'X-Authkey' => $request->getHeaderLine('X-Authkey'),
            'entity_id' => $entity->getId()
        ]);

        if (null === $state || $request->getHeaderLine('X-Authkey') !== $state) {
            throw new \BO\Zmsapi\Exception\Workstation\WorkstationAuthFailed();
        }
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        if ((new Useraccount())->readIsUserExisting($entity->getId())) {
            $workstation = $this->getLoggedInWorkstationByOidc($request, $entity, $resolveReferences);
        } else {
            throw new \BO\Zmsapi\Exception\Useraccount\UseraccountNotFound();
        }
        \BO\Zmsdb\Connection\Select::writeCommit();

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function getLoggedInWorkstationByOidc($request, $entity, $resolveReferences)
    {
        Helper\UserAuth::testUseraccountExists($entity->getId());

        $workstation = (new Helper\User($request, $resolveReferences))->readWorkstation();
        Helper\User::testWorkstationIsOveraged($workstation);

        WorkstationLogin::testLoginHash($workstation);
        $workstation = (new Workstation())->writeEntityLoginByOidc(
            $entity->id,
            $request->getHeaderLine('X-Authkey'),
            \App::getNow(),
            (new \DateTime())->setTimestamp(time() + \App::SESSION_DURATION),
            $resolveReferences
        );
        return $workstation;
    }
}

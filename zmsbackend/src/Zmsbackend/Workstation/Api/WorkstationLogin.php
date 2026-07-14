<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Log\Service\Log;
use BO\Zmsbackend\Workstation\Service\Workstation;
use BO\Zmsbackend\Useraccount\Service\Useraccount;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationLogin extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Useraccount($input);
        $entity->testValid();

        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $workstation = self::getLoggedInWorkstation($request, $entity, $resolveReferences);
        \BO\Zmsbackend\Connection\Select::writeCommit(); // @codeCoverageIgnore

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    public static function getLoggedInWorkstation($request, $entity, $resolveReferences)
    {
        \BO\Zmsbackend\Helper\UserAuth::testUseraccountExists($entity->getId());
        $useraccount = \BO\Zmsbackend\Helper\UserAuth::getVerifiedUseraccount($entity);
        \BO\Zmsbackend\Helper\UserAuth::testPasswordMatching($useraccount, $entity->password);

        $workstation = (new \BO\Zmsbackend\Helper\User($request, $resolveReferences))->readWorkstation();
        \BO\Zmsbackend\Helper\User::testWorkstationIsOveraged($workstation);

        static::testLoginHash($workstation);
        $workstation = (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeEntityLoginByName(
            $useraccount->id,
            $useraccount->password,
            \App::getNow(),
            (new \DateTime())->setTimestamp(time() + \App::SESSION_DURATION),
            $resolveReferences
        );

        \BO\Zmsbackend\Log\Service\Log::writeLogEntry(
            "LOGIN (WorkstattionLogin::getLoggedInWorkstation) " . $useraccount->id,
            0,
            \BO\Zmsbackend\Log\Service\Log::PROCESS,
            $workstation->getScope()->getId(),
            $workstation->getUseraccount()->getId()
        );

        return $workstation;
    }

    public static function testLoginHash($workstation)
    {
        $useraccount = $workstation->getUseraccount();
        if (isset($useraccount->id)) {
            $logInHash = (new \BO\Zmsbackend\Workstation\Service\Workstation())->readLoggedInHashByName($useraccount->id);
            if (null !== $logInHash) {
                $exception = new \BO\Zmsbackend\Useraccount\Exception\UserAlreadyLoggedIn();
                $exception->data = $workstation;
                throw $exception;
            }
        }
    }
}

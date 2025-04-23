<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Log;
use BO\Zmsdb\Workstation;
use BO\Zmsdb\Useraccount;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationLogin extends BaseController
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
        $resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Useraccount($input);
        $entity->testValid();

        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $workstation = self::getLoggedInWorkstation($request, $entity, $resolveReferences);
        \BO\Zmsdb\Connection\Select::writeCommit(); // @codeCoverageIgnore

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    public static function getLoggedInWorkstation($request, $entity, $resolveReferences)
    {
        Helper\UserAuth::testUseraccountExists($entity->getId());
        $useraccount = Helper\UserAuth::getVerifiedUseraccount($entity);
        Helper\UserAuth::testPasswordMatching($useraccount, $entity->password);

        $workstation = (new Helper\User($request, $resolveReferences))->readWorkstation();
        Helper\User::testWorkstationIsOveraged($workstation);

        static::testLoginHash($workstation);
        $workstation = (new Workstation())->writeEntityLoginByName(
            $useraccount->id,
            $useraccount->password,
            \App::getNow(),
            (new \DateTime())->setTimestamp(time() + \App::SESSION_DURATION),
            $resolveReferences
        );

        \BO\Zmsdb\Log::writeLogEntry(
            "LOGIN (WorkstattionLogin::getLoggedInWorkstation) " . $useraccount->id,
            0,
            Log::PROCESS,
            $workstation->getScope()->getId(),
            $workstation->getUseraccount()->getId()
        );

        return $workstation;
    }

    public static function testLoginHash($workstation)
    {
        $useraccount = $workstation->getUseraccount();
        if (isset($useraccount->id)) {
            $logInHash = (new Workstation())->readLoggedInHashByName($useraccount->id);
            if (null !== $logInHash) {
                $exception = new \BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn();
                $exception->data = $workstation;
                throw $exception;
            }
        }
    }
}

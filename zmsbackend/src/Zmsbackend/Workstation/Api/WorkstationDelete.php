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

class WorkstationDelete extends \BO\Zmsbackend\Api\BaseController
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
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        if (! (new \BO\Zmsbackend\Useraccount\Service\Useraccount())->readIsUserExisting($args['loginname'])) {
            throw new \BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound();
        }
        if ($workstation->process && $workstation->process->hasId()) {
            throw new \BO\Zmsbackend\Workstation\Exception\WorkstationHasCalledProcess();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeEntityLogoutByName($args['loginname'], $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());

        \BO\Zmsbackend\Log\Service\Log::writeLogEntry(
            "LOGOUT (WorkstattionDelete::readResponse) " . $args['loginname'],
            0,
            \BO\Zmsbackend\Log\Service\Log::PROCESS,
            $workstation->getScope()->getId(),
            $workstation->getUseraccount()->getId()
        );

        return $response;
    }
}

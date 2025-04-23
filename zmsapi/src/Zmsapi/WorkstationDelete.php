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

class WorkstationDelete extends BaseController
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
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $workstation = (new Helper\User($request, 1))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        if (! (new Useraccount())->readIsUserExisting($args['loginname'])) {
            throw new Exception\Useraccount\UseraccountNotFound();
        }
        if ($workstation->process && $workstation->process->hasId()) {
            throw new Exception\Workstation\WorkstationHasCalledProcess();
        }

        $message = Response\Message::create($request);
        $message->data = (new Workstation())->writeEntityLogoutByName($args['loginname'], $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());

        \BO\Zmsdb\Log::writeLogEntry(
            "LOGOUT (WorkstattionDelete::readResponse) " . $args['loginname'],
            0,
            Log::PROCESS,
            $workstation->getScope()->getId(),
            $workstation->getUseraccount()->getId()
        );

        return $response;
    }
}

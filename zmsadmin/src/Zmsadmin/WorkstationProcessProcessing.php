<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Exception\WorkstationMissingAssignedProcess;
use BO\Zmsentities\Process;
use BO\Zmsentities\Workstation;

class WorkstationProcessProcessing extends BaseController
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
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (!$workstation instanceof Workstation) {
            throw new WorkstationMissingAssignedProcess();
        }
        if (! $workstation->process->hasId()) {
            throw new WorkstationMissingAssignedProcess();
        }

        // Re-saving an already processing process resets showUpTime (Bearbeitungszeit).
        // Only transition called → processing here; otherwise just render the current view.
        if ($workstation->process->getStatus() !== 'processing') {
            $workstation->process->status = 'processing';
            $workstation->process->parkedBy = null;
            $savedProcess = \App::$http->readPostResult(
                '/process/' . $workstation->process->id . '/' . $workstation->process->authKey . '/',
                $workstation->process,
                ['initiator' => 'admin']
            )->getEntity();
            if (!$savedProcess instanceof Process) {
                throw new WorkstationMissingAssignedProcess();
            }
            $workstation->process = $savedProcess;
        }

        $validator = $request->getAttribute('validator');
        $error = $validator->getParameter('error')->isString()->getValue();

        return Render::withHtml(
            $response,
            'block/process/info.twig',
            array(
                'workstation' => $workstation,
                'error' => $error
            )
        );
    }
}

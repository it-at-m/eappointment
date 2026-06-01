<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

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
        $workstation->process->status = 'processing';
        $workstation->process->parkedBy = null;
        if (! $workstation->process->hasId()) {
            throw new \BO\Zmsentities\Exception\WorkstationMissingAssignedProcess();
        }
        $workstation->process = \App::$http->readPostResult(
            '/process/' . $workstation->process->id . '/' . $workstation->process->authKey . '/',
            $workstation->process,
            ['initiator' => 'admin']
        )->getEntity();

        $validator = $request->getAttribute('validator');
        $error = $validator->getParameter('error')->isString()->getValue();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/info.twig',
            array(
                'workstation' => $workstation,
                'error' => $error
            )
        );
    }
}

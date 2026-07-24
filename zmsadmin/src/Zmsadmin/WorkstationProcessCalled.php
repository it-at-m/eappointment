<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

class WorkstationProcessCalled extends BaseController
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $validator = $request->getAttribute('validator');
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        if (! $workstation->process->hasId() && ! $workstation->process->queue->callTime) {
            $process = \App::$http->readGetResult('/process/' . $args['id'] . '/')->getEntity();
            try {
                $workstation = \App::$http->readPostResult('/workstation/process/called/', $process, [
                    'allowClusterWideCall' => \App::$allowClusterWideCall
                ])->getEntity();
            } catch (\BO\Zmsclient\Exception $e) {
                if ($e->template === 'BO\\Zmsbackend\\Process\\Exception\\ProcessAlreadyCalled') {
                    return \BO\Slim\Render::redirect(
                        'workstationProcessCalled',
                        ['id' => $processId],
                        ['error' => 'has_called_process']
                    );
                }

                throw $e;
            }
        }

        $excludedIds = Helper\ExcludeIds::fromQuery(
            $validator->getParameter('exclude')->isString()->setDefault('')->getValue()
        );
        $excludedIds[] = $workstation->process->toQueue(\App::$now)->number;

        $error = $validator->getParameter('error')->isString()->getValue();
        if (isset($processId) && $workstation->process->getId() != $processId) {
            $error = 'has_called_process';
        }

        if ($workstation->process->getStatus() == 'processing') {
            return \BO\Slim\Render::redirect('workstationProcessProcessing', [], ['error' => $error]);
        }

        // Check if $process is set or assign a default value (like null)
        $process = isset($process) ? $process : null;

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/called.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'menuActive' => 'workstation',
                'process' => $process,
                'exclude' => Helper\ExcludeIds::toQuery($excludedIds),
                'error' => $error
            )
        );
    }
}

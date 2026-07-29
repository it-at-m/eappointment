<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use BO\Zmsadmin\Helper\ProcessFinishedHelper;
use BO\Zmsentities\Exception\WorkstationMissingAssignedProcess;
use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Workstation;

class WorkstationProcessFinished extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $this->testProcess($workstation);
        $input = $request->getParsedBody();
        $validator = $request->getAttribute('validator');
        $nextProcessId = $validator->getParameter('nextprocess')->isNumber()->getValue();
        if (!$nextProcessId && is_array($input) && isset($input['nextprocess'])) {
            $nextProcessId = Validator::value($input['nextprocess'])->isNumber()->getValue();
        }
        if ($nextProcessId && ! \App::$allowClusterWideCall) {
            $nextProcess = \App::$http->readGetResult('/process/' . $nextProcessId . '/')->getEntity();
            $workstation->validateProcessScopeAccess($workstation->getScopeList(), $nextProcess);
        }
        $statisticEnabled = $workstation->getScope()->getPreference('queue', 'statisticsEnabled');

        if (! $statisticEnabled) {
            $workstation->process['status'] = 'finished';
            return $this->getFinishedResponse($workstation, null, $nextProcessId);
        }

        $scopeId = $workstation->scope['id'];
        if (! empty($workstation->process)) {
            $scopeId = $workstation->process->scope->id;
        }

        $requestList = \App::$http
            ->readGetResult('/scope/' . $scopeId . '/request/')
            ->getCollection();
        $requestList = $requestList ? $requestList : new RequestList();

        if (is_array($input) && isset($input['process']) && array_key_exists('id', $input['process'])) {
            $source = $workstation->getScope()->getSource();
            $process = new ProcessFinishedHelper(clone $workstation->process, $input, $requestList, $source);
            return $this->getFinishedResponse($workstation, $process, $nextProcessId);
        }

        return Render::withHtml(
            $response,
            'page/workstationProcessFinished.twig',
            array(
                'title' => 'Kundendaten',
                'workstation' => $workstation,
                'requestList' => $requestList->toSortedByGroup(),
                'menuActive' => 'workstation',
                'statisticEnabled' => $statisticEnabled,
                'nextProcessId' => $nextProcessId
            )
        );
    }

    protected function getFinishedResponse(
        Workstation $workstation,
        Process $process = null,
        $nextProcessId = null
    ) {
        $process = ($process) ? $process : clone $workstation->process;
        $process->status = ('pending' != $process->status) ? 'finished' : $process->status;
        \App::$http->readPostResult('/process/status/finished/', new Process($process))->getEntity();
        $redirectParams = [];
        if ($nextProcessId) {
            $redirectParams['calledprocess'] = $nextProcessId;
        }
        return Render::redirect(
            $workstation->getVariantName(),
            array(),
            $redirectParams
        );
    }


    protected function testProcess(Workstation $workstation)
    {
        if (! $workstation->process->hasId()) {
            throw new WorkstationMissingAssignedProcess();
        }
    }
}

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
use BO\Zmsentities\Requeststatistic;
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
        if (!$workstation instanceof Workstation) {
            throw new WorkstationMissingAssignedProcess();
        }
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
            $workstation->getProcess()['status'] = 'finished';
            return $this->getFinishedResponse($workstation, null, $nextProcessId);
        }

        $scopeId = $workstation->getProcess()->getCurrentScope()['id']
            ?? $workstation->getScope()['id'];

        $requestStatistic = $this->readRequeststatistic((int) $scopeId);
        $scopeRequestList = $requestStatistic->getScopeRequests();
        $additionalDepartmentRequestList = $requestStatistic->getAdditionalDepartmentRequests();

        $selectableRequestList = (new RequestList())
            ->addList($scopeRequestList)
            ->addList($additionalDepartmentRequestList);

        if (is_array($input) && isset($input['process']) && array_key_exists('id', $input['process'])) {
            $source = $workstation->getScope()->getSource();
            $process = new ProcessFinishedHelper(
                clone $workstation->getProcess(),
                $input,
                $selectableRequestList,
                $source
            );
            return $this->getFinishedResponse($workstation, $process, $nextProcessId);
        }

        return Render::withHtml(
            $response,
            'page/workstationProcessFinished.twig',
            array(
                'title' => 'Kundendaten',
                'workstation' => $workstation,
                'scopeRequestList' => $scopeRequestList->toSortedByGroup(),
                'additionalDepartmentRequestList' => $additionalDepartmentRequestList->toSortedByGroup(),
                'menuActive' => 'workstation',
                'statisticEnabled' => $statisticEnabled,
                'nextProcessId' => $nextProcessId
            )
        );
    }

    protected function readRequeststatistic(int $scopeId): Requeststatistic
    {
        $entity = \App::$http
            ->readGetResult('/scope/' . $scopeId . '/request/department/')
            ->getEntity();

        if ($entity instanceof Requeststatistic) {
            return $entity;
        }

        throw new \RuntimeException(
            'Invalid API response for /scope/' . $scopeId . '/request/department/'
        );
    }

    protected function getFinishedResponse(
        Workstation $workstation,
        ?Process $process = null,
        ?int $nextProcessId = null
    ): \BO\Slim\Response {
        $process ??= clone $workstation->getProcess();
        $process['status'] = ('pending' != $process['status']) ? 'finished' : $process['status'];
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


    /**
     * @return void
     */
    protected function testProcess(Workstation $workstation)
    {
        if (! $workstation->getProcess()->hasId()) {
            throw new WorkstationMissingAssignedProcess();
        }
    }
}

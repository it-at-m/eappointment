<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

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
        $this->testProcess($workstation);
        $input = $request->getParsedBody();
        $statisticEnabled = $workstation->getScope()->getPreference('queue', 'statisticsEnabled');

        if (! $statisticEnabled) {
            $workstation->process['status'] = 'finished';
            return $this->getFinishedResponse($workstation);
        }

        $scopeId = $workstation->scope['id'];
        if (! empty($workstation->process)) {
            $scopeId = $workstation->process->scope->id;
        }

        $requestStatistic = $this->readRequeststatistic((int) $scopeId);
        $scopeRequestList = $requestStatistic->scope instanceof RequestList
            ? $requestStatistic->scope
            : new RequestList();
        $outsideScopeRequestList = $requestStatistic->outsideScope instanceof RequestList
            ? $requestStatistic->outsideScope
            : new RequestList();

        $selectableRequestList = (new RequestList())
            ->addList($scopeRequestList)
            ->addList($outsideScopeRequestList);

        if (is_array($input) && isset($input['process']) && array_key_exists('id', $input['process'])) {
            $source = $workstation->getScope()->getSource();
            $process = new ProcessFinishedHelper(clone $workstation->process, $input, $selectableRequestList, $source);
            return $this->getFinishedResponse($workstation, $process);
        }

        return Render::withHtml(
            $response,
            'page/workstationProcessFinished.twig',
            array(
                'title' => 'Kundendaten',
                'workstation' => $workstation,
                'requestList' => $scopeRequestList->toSortedByGroup(),
                'outsideScopeRequestList' => $outsideScopeRequestList->toSortedByGroup(),
                'menuActive' => 'workstation',
                'statisticEnabled' => $statisticEnabled
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

        \App::$log->error('Expected Requeststatistic from department request list API', [
            'scopeId' => $scopeId,
            'receivedType' => is_object($entity) ? get_class($entity) : gettype($entity),
        ]);
        throw new \RuntimeException(
            'Invalid API response for /scope/' . $scopeId . '/request/department/'
        );
    }

    protected function getFinishedResponse(
        Workstation $workstation,
        Process $process = null
    ) {
        $process = ($process) ? $process : clone $workstation->process;
        $process->status = ('pending' != $process->status) ? 'finished' : $process->status;
        \App::$http->readPostResult('/process/status/finished/', new Process($process))->getEntity();
        return Render::redirect(
            $workstation->getVariantName(),
            array(),
            array()
        );
    }


    protected function testProcess(Workstation $workstation)
    {
        if (! $workstation->process->hasId()) {
            throw new WorkstationMissingAssignedProcess();
        }
    }
}

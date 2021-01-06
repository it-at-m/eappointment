<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \Psr\Http\Message\RequestInterface;
use \BO\Zmsadmin\Helper\ProcessFinishedHelper;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Collection\RequestList;

class WorkstationProcessFinished extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $department = \App::$http
            ->readGetResult(
                '/scope/' . $workstation->scope['id'] . '/department/',
                ['resolveReferences' => 2]
            )->getEntity();
        $this->testProcess($workstation);
        $input = $request->getParsedBody();
        $statisticEnabled = $workstation->getScope()->getPreference('queue', 'statisticsEnabled');
        $isDefaultPickup = $workstation->getScope()->getPreference('pickup', 'isDefault');

        if (! $statisticEnabled && ! $isDefaultPickup) {
            $workstation->process['status'] = 'finished';
            return $this->getFinishedResponse($workstation);
        }

        $requestList = \App::$http
            ->readGetResult('/scope/'. $workstation->scope['id'] .'/request/')
            ->getCollection();
        $requestList = $requestList ? $requestList : new RequestList();

        if (is_array($input) && isset($input['process']) && array_key_exists('id', $input['process'])) {
            $source = $workstation->getScope()->getSource();
            $process = new ProcessFinishedHelper(clone $workstation->process, $input, $requestList, $source);
            return $this->getFinishedResponse($workstation, $process);
        }
 
        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstationProcessFinished.twig',
            array(
                'title' => 'Kundendaten',
                'workstation' => $workstation,
                'pickupList' => $department->getScopeList(),
                'requestList' => $requestList->toSortedByGroup(),
                'menuActive' => 'workstation',
                'statisticEnabled' => $statisticEnabled,
                'isDefaultPickup' => $isDefaultPickup
            )
        );
    }

    protected function getFinishedResponse(
        \BO\Zmsentities\Workstation $workstation,
        Entity $process = null
    ) {
        $process = ($process) ? $process : clone $workstation->process;
        $process->status = ('pending' != $process->status) ? 'finished' : $process->status;
        \App::$http->readPostResult('/process/status/finished/', new Entity($process))->getEntity();
        return \BO\Slim\Render::redirect(
            $workstation->getVariantName(),
            array(),
            array()
        );
    }


    protected function testProcess(\BO\Zmsentities\Workstation $workstation)
    {
        if (! $workstation->process->hasId()) {
            throw new \BO\Zmsentities\Exception\WorkstationMissingAssignedProcess();
        }
    }
}

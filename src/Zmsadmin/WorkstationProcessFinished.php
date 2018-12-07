<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \Psr\Http\Message\RequestInterface;
use \BO\Zmsadmin\Helper\ProcessFinishedHelper;
use \BO\Zmsentities\Process as Entity;

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
        $this->testProcess($workstation);
        $requestList = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/request/')->getCollection();

        $statisticEnabled = $workstation->getScope()->getPreference('queue', 'statisticsEnabled');
        $isDefaultPickup = $workstation->getScope()->getPreference('pickup', 'isDefault');

        $workstation->process['status'] = (! $statisticEnabled && $isDefaultPickup) ? 'pending' : 'finished';
        $process = new ProcessFinishedHelper(clone $workstation->process);
        $input = $request->getParsedBody();

        if (! $statisticEnabled && ! $isDefaultPickup) {
            return $this->getResponseWithStatisticDisabled($process, $workstation);
        }

        if (is_array($input) && array_key_exists('id', $input['process'])) {
            return $this->getResponseWithStatisticEnabled(
                $input,
                $process,
                $workstation,
                $requestList
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstationProcessFinished.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'pickupList' => $workstation->getScopeList(),
                'requestList' => $requestList->toSortedByGroup(),
                'menuActive' => 'workstation',
                'statisticEnabled' => $statisticEnabled,
                'isDefaultPickup' => $isDefaultPickup
            )
        );
    }

    protected function testProcess(\BO\Zmsentities\Workstation $workstation)
    {
        if (! $workstation->process->hasId()) {
            throw new \BO\Zmsentities\Exception\WorkstationMissingAssignedProcess();
        }
    }

    protected function getResponseWithStatisticDisabled($process, $workstation)
    {
        \App::$http->readPostResult('/process/status/finished/', new Entity($process))->getEntity();
        return \BO\Slim\Render::redirect(
            $workstation->getVariantName(),
            array(),
            array()
        );
    }

    protected function getResponseWithStatisticEnabled(
        array $input,
        \BO\Zmsentities\Process $process,
        \BO\Zmsentities\Workstation $workstation,
        \BO\Zmsentities\Collection\RequestList $requestList
    ) {
        $firstClient = $process->getFirstClient();
        $process->addData($input['process']);
        $process->setClientData($input, $firstClient);
        $process->setPickupData($input);
        $process->setRequestData($input, $requestList, $workstation);
        $process->setClientsCount($input['statistic']['clientsCount']);
        \App::$http->readPostResult('/process/status/finished/', new Entity($process))->getEntity();
        return \BO\Slim\Render::redirect(
            $workstation->getVariantName(),
            array(),
            array()
        );
    }
}

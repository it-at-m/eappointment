<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \Psr\Http\Message\RequestInterface;

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
        $process = clone $workstation->process;
        $input = $request->getParsedBody();

        if (! $statisticEnabled && ! $isDefaultPickup) {
            return $this->getResponseWithStatisticDisabled($process, $workstation);
        }

        if (is_array($input) && array_key_exists('id', $input['process'])) {
            return $this->getResponseWithStatisticEnabled($input, $process, $workstation);
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
        $process = \App::$http->readPostResult('/process/status/finished/', $process)->getEntity();
        //$workstation = \App::$http->readDeleteResult('/workstation/process/')->getEntity();
        return \BO\Slim\Render::redirect(
            $workstation->getVariantName(),
            array(),
            array()
        );
    }

    protected function getResponseWithStatisticEnabled(array $input, \BO\Zmsentities\Process $process, $workstation)
    {
        $process->addData($input['process']);
        //pickup
        if (array_key_exists('pickupScope', $input) && 0 != $input['pickupScope']) {
            $process->status = 'pending';
            $process->scope['id'] = $input['pickupScope'];
        }
        $process->setClientsCount($input['statistic']['clientsCount']);
        $process = \App::$http->readPostResult('/process/status/finished/', $process)->getEntity();
        //$workstation = \App::$http->readDeleteResult('/workstation/process/')->getEntity();
        return \BO\Slim\Render::redirect(
            $workstation->getVariantName(),
            array(),
            array()
        );
    }
}

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
            return $this->getResponseWithStatisticEnabled($input, $process, $workstation, $requestList);
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

    protected function getResponseWithStatisticEnabled(
        array $input,
        \BO\Zmsentities\Process $process,
        \BO\Zmsentities\Workstation $workstation,
        \BO\Zmsentities\Collection\RequestList $requestList
    ) {
        $process->addData($input['process']);
        //pickup
        if (array_key_exists('pickupScope', $input) && 0 != $input['pickupScope']) {
            $process->status = 'pending';
            $process->scope['id'] = $input['pickupScope'];
        }
        if (array_key_exists('ignoreRequests', $input) && $input['ignoreRequests']) {
            $process->requests = new \BO\Zmsentities\Collection\RequestList();
            $request = new \BO\Zmsentities\Request([
                'id' => -1,
                'source' => $workstation->getScope()->getSource(),
                'name' =>  "Ohne Erfassung",
            ]);
            $process->requests[] = $request;
        } elseif (array_key_exists('noRequestsPerformed', $input) && $input['noRequestsPerformed']) {
            $process->requests = new \BO\Zmsentities\Collection\RequestList();
            $request = new \BO\Zmsentities\Request([
                'id' => 0,
                'source' => $workstation->getScope()->getSource(),
                'name' =>  "Dienstleistung konnte nicht erbracht werden",
            ]);
            $process->requests[] = $request;
        } elseif (array_key_exists('requestCountList', $input)) {
            $process->requests = $requestList->withCountList($input['requestCountList']);
        }
        $process->setClientsCount($input['statistic']['clientsCount']);
        //throw new \Exception("Test");
        $process = \App::$http->readPostResult('/process/status/finished/', $process)->getEntity();
        //$workstation = \App::$http->readDeleteResult('/workstation/process/')->getEntity();
        return \BO\Slim\Render::redirect(
            $workstation->getVariantName(),
            array(),
            array()
        );
    }
}

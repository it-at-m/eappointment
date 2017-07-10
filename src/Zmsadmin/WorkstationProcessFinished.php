<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class WorkstationProcessFinished extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $requestList = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/request/')->getCollection();

        $statisticEnabled = $workstation->getScope()->getPreference('queue', 'statisticsEnabled');
        $isDefaultPickup = $workstation->getScope()->getPreference('pickup', 'isDefault');
        $workstation->process['status'] = (! $statisticEnabled && $isDefaultPickup) ? 'pending' : 'finished';
        $process = clone $workstation->process;

        if (! $statisticEnabled && ! $isDefaultPickup) {
            $process = \App::$http->readPostResult('/process/status/finished/', $process)->getEntity();
            $workstation = \App::$http->readDeleteResult('/workstation/process/')->getEntity();
            return \BO\Slim\Render::redirect(
                $workstation->getVariantName(),
                array(),
                array()
            );
        } else {
            $input = $request->getParsedBody();
            if (is_array($input) && array_key_exists('id', $input['process'])) {
                $process->addData($input['process']);
                //pickup
                if (array_key_exists('pickupScope', $input) && 0 != $input['pickupScope']) {
                    $process->status = 'pending';
                    $process->scope['id'] = $input['pickupScope'];
                }
                $process->setClientsCount($input['statistic']['clientsCount']);
                $process = \App::$http->readPostResult('/process/status/finished/', $process)->getEntity();
                $workstation = \App::$http->readDeleteResult('/workstation/process/')->getEntity();
                return \BO\Slim\Render::redirect(
                    $workstation->getVariantName(),
                    array(),
                    array()
                );
            }
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
}

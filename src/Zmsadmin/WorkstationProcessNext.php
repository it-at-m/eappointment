<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

class WorkstationProcessNext extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $validator = $request->getAttribute('validator');
        $excludedIds = $validator->getParameter('exclude')->isString()->getValue();
        $excludedIds = ($excludedIds) ? $excludedIds : '';

        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
            $process = \App::$http
                ->readGetResult('/cluster/'. $cluster['id'] .'/queue/next/', ['exclude' => $excludedIds])
                ->getEntity();
        } else {
            $process = \App::$http
                ->readGetResult('/scope/'. $workstation->scope['id'] .'/queue/next/', ['exclude' => $excludedIds])
                ->getEntity();
        }

        if ($process->toProperty()->amendment->get()) {
            return Helper\Render::redirect(
                'workstationProcessPreCall',
                array(
                    'id' => $process->id,
                    'authkey' => $process->authKey
                ),
                array(
                    'exclude' => $excludedIds
                )
            );
        }
        return Helper\Render::redirect(
            'workstationProcessCalled',
            array(
                'id' => $process->id,
                'authkey' => $process->authKey
            ),
            array(
                'exclude' => $excludedIds
            )
        );
    }
}

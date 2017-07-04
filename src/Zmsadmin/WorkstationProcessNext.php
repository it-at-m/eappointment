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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $validator = $request->getAttribute('validator');
        $excludedIds = $validator->getParameter('exclude')->isString()->getValue();
        $excludedIds = ($excludedIds) ? $excludedIds : '';
        $process = (new Helper\ClusterHelper($workstation))->getNextProcess($excludedIds);

        if ($process->toProperty()->amendment->get()) {
            return \BO\Slim\Render::redirect(
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
        return \BO\Slim\Render::redirect(
            'workstationProcessCalled',
            array(
                'id' => $process->id
            ),
            array(
                'exclude' => $excludedIds
            )
        );
    }
}

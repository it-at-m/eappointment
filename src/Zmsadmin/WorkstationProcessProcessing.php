<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class WorkstationProcessProcessing extends BaseController
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
        $workstationInfo = Helper\WorkstationInfo::getInfoBoxData($workstation);
        $process = new \BO\Zmsentities\Process($workstation->process);
        $process->status = 'processing';
        $process = \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/',
            $process
        )->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/info.twig',
            array(
                'workstation' => $workstation,
                'workstationInfo' => $workstationInfo,
                'process' => $process
            )
        );
    }
}

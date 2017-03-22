<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Init Controller to display next Button Template only
  *
  */
class WorkstationProcessCancelNext extends BaseController
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
        $workstation->process['queue']['callCount']++;
        $workstation->process = (new \BO\Zmsentities\Process($workstation->process))->setStatusBySettings();
        $workstation = \App::$http->readPostResult('/workstation/process/delete/', $workstation)->getEntity();
        return \BO\Slim\Render::redirect(
            'workstationProcessNext',
            array(),
            array()
        );
    }
}

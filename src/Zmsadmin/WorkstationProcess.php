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
class WorkstationProcess extends BaseController
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
        $workstationInfo = Helper\WorkstationInfo::getInfoBoxData($workstation);
        $template = ($workstation->process->hasId()) ? 'info' : 'next';
        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/'. $template .'.twig',
            array(
                'workstation' => $workstation,
                'workstationInfo' => $workstationInfo
            )
        );
    }
}

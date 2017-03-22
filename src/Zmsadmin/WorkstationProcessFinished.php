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
class WorkstationProcessFinished extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 3])->getEntity();
        $workstationInfo = Helper\WorkstationInfo::getInfoBoxData($workstation);

        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstationClientProcessed.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'workstationInfo' => $workstationInfo,
                'menuActive' => 'workstation'
            )
        );
    }
}

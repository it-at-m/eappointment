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
class WorkstationClientFinished extends BaseController
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
        $workstation->hasDepartmentList();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstationClientProcessed.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'menuActive' => 'workstation'
            )
        );
    }
}

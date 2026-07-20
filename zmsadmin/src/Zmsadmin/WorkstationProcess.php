<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use DateTime;

/**
  * Init Controller to display next Button Template only
  *
  */
class WorkstationProcess extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $template = ($workstation->process->hasId() && 'processing' == $workstation->process->status) ? 'info' : 'next';
        $selectedDate = (new DateTime())->format('Y-m-d');
        if ($workstation->process->hasId() && 'called' == $workstation->process->getStatus()) {
            return \BO\Slim\Render::redirect(
                'workstationProcessCalled',
                array(
                    'id' => $workstation->process->id
                )
            );
        }
        $workstationInfo = Helper\WorkstationInfo::getInfoBoxData($workstation, $selectedDate);
        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/' . $template . '.twig',
            array(
                'workstation' => $workstation,
                'workstationInfo' => $workstationInfo,
                'selectedDate' => $selectedDate
            )
        );
    }
}

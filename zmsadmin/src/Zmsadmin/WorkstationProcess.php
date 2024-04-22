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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $template = ($workstation->process->hasId() && 'processing' == $workstation->process->status) ? 'info' : 'next';
        if ($workstation->process->hasId() && 'called' == $workstation->process->getStatus()) {
            return \BO\Slim\Render::redirect(
                'workstationProcessCalled',
                array(
                    'id' => $workstation->process->id
                )
            );
        }
        error_log("hey");
        error_log($template);
        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/'. $template .'.twig',
            array(
                'workstation' => $workstation
            )
        );
    }
}

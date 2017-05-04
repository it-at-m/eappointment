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
class PickupDelete extends BaseController
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
        $workstation->process['status'] = 'finished';
        $process = \App::$http->readPostResult('/process/status/finished/', $workstation->process)->getEntity();
        if ($workstation) {
            return \BO\Slim\Render::withHtml(
                $response,
                'block/pickup/deleted.twig',
                array(
                    'workstation' => $workstation,
                    'process' => $process
                )
            );
        }
    }
}

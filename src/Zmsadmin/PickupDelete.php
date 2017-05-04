<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

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
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/workstation/process/'. $processId .'/get/')->getEntity();
        $process->status = 'finished';
        \App::$http->readDeleteResult('/workstation/process/delete/');
        $archive = \App::$http->readPostResult('/process/status/finished/', $process)->getEntity();
        
        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/deleted.twig',
            array(
                'workstation' => $workstation,
                'process' => $process,
                'archive' => $archive
            )
        );
    }
}

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $processIdList = Validator::value($args['ids'])->isString()->getValue();
        $idList = explode(',', $processIdList);
        foreach ($idList as $processId) {
            $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
            $process->status = 'finished';
            \App::$http->readDeleteResult('/workstation/process/');
            $processArchived = \App::$http->readPostResult('/process/status/finished/', $process)->getEntity();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/deleted.twig',
            array(
                'idList' => $idList,
                'workstation' => $workstation,
                'process' => $process,
                'archive' => $processArchived,
            )
        );
    }
}

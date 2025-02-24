<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $deleteList = Validator::param('list')->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/' . $processId . '/')->getEntity();
        $process->status = 'finished';
        \App::$http->readDeleteResult('/workstation/process/');
        $processArchived = \App::$http
            ->readPostResult('/process/status/finished/', $process, ['survey' => 0])
            ->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/deleted.twig',
            array(
                'deleteList' => $deleteList,
                'workstation' => $workstation,
                'process' => $process,
                'archive' => $processArchived,
            )
        );
    }
}

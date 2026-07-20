<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;

class WorkstationProcessParked extends BaseController
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
        $validator = $request->getAttribute('validator');
        $noRedirect = $validator->getParameter('noredirect')->isNumber()->getValue();
        if ($workstation->process['id']) {
            \App::$http->readDeleteResult('/workstation/process/parked/')->getEntity();
        }
        if (1 == $noRedirect) {
            return $response;
        }
        return Render::redirect(
            'workstationProcessCallButton',
            array(),
            array()
        );
    }
}

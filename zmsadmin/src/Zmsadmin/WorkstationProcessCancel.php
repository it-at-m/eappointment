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
class WorkstationProcessCancel extends BaseController
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
        $validator = $request->getAttribute('validator');
        $noRedirect = $validator->getParameter('noredirect')->isNumber()->getValue();
        $requeue = $validator->getParameter('requeue')->isNumber()->setDefault(0)->getValue();

        if ($workstation->process['id']) {
            $deleteParameters = [];
            if (1 === (int) $requeue) {
                $deleteParameters['requeue'] = 1;
            }
            \App::$http->readDeleteResult('/workstation/process/', $deleteParameters)->getEntity();
        }
        if (1 == $noRedirect) {
            return $response;
        }
        return \BO\Slim\Render::redirect(
            'workstationProcessCallButton',
            array(),
            array()
        );
    }
}

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
        $action = strtolower((string) $validator->getParameter('action')->isString()->setDefault('')->getValue());

        if ($workstation->process['id']) {
            $deleteParameters = [];
            if ($action !== '') {
                $deleteParameters['action'] = $action;
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

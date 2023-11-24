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
class WorkstationProcessCancelNext extends BaseController
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
        $excludedIds = $validator->getParameter('exclude')->isString()->getValue();
        $excludedIds = ($excludedIds) ? $excludedIds : '';
        if ($workstation->process['id']) {
            \App::$http->readDeleteResult('/workstation/process/')->getEntity();
        }        

        return \BO\Slim\Render::redirect(
            'workstationProcessNext',
            array(),
            array(
                'exclude' => $excludedIds
            )
        );
    }
}

<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsadmin\Helper\ExcludeIds;

/**
  * Init Controller to display next Button Template only
  *
  */
class WorkstationProcessCancelNext extends BaseController
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
        $excludedIds = ExcludeIds::fromQuery(
            $validator->getParameter('exclude')->isString()->getValue()
        );
        if ($workstation->process['id']) {
            \App::$http->readDeleteResult('/workstation/process/', ['action' => 'requeue_and_skip_to_next'])->getEntity();
        }
        return \BO\Slim\Render::redirect(
            'workstationProcessNext',
            array(),
            array(
                'exclude' => ExcludeIds::toQuery($excludedIds)
            )
        );
    }
}

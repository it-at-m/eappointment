<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Init Controller to cancel called pickup
  *
  */
class PickupCallCancel extends BaseController
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

        if ($workstation->process['id']) {
            \App::$http->readDeleteResult('/workstation/process/');
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/canceled.twig',
            array()
        );
    }
}

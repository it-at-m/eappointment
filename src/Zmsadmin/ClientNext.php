<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

class ClientNext extends BaseController
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
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $date = ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d');

        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
            $process = \App::$http->readGetResult(
                '/cluster/'. $cluster['id'] .'/queue/next/',
                ['date' => $date]
            )->getEntity();
        } else {
            $process = \App::$http->readGetResult(
                '/scope/'. $workstation->scope['id'] .'/queue/next/',
                ['date' => $date]
            )->getEntity();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'block/client/next.twig',
            array(
                'process' => $process
            )
        );
    }
}

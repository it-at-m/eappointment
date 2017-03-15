<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

class WorkstationProcessNext extends BaseController
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
            $queueListCalled = \App::$http->readGetResult(
                '/cluster/'. $cluster['id'] .'/queue/',
                ['date' => $date]
            )->getCollection()->withStatus(['called'])->getWaitingNumberListCsv();
            $process = \App::$http->readGetResult(
                '/cluster/'. $cluster['id'] .'/queue/next/',
                ['date' => $date, 'exclude' => $queueListCalled]
            )->getEntity();
        } else {
            $queueListCalled = \App::$http->readGetResult(
                '/scope/'. $workstation->scope['id'] .'/queue/',
                ['date' => $date]
            )->getCollection()->withStatus(['called'])->getWaitingNumberListCsv();
            $process = \App::$http->readGetResult(
                '/scope/'. $workstation->scope['id'] .'/queue/next/',
                ['date' => $date, 'exclude' => $queueListCalled]
            )->getEntity();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/next.twig',
            array(
                'process' => $process
            )
        );
    }
}

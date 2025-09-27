<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Overview extends BaseController
{
    protected $resolveLevel = 3;

    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $waitingPeriod = \App::$http
            ->readGetResult('/warehouse/waitingscope/' . $this->workstation->scope['id'] . '/')
            ->getEntity();
        $clientPeriod = \App::$http
            ->readGetResult('/warehouse/clientscope/' . $this->workstation->scope['id'] . '/')
            ->getEntity();
        $requestPeriod = \App::$http
            ->readGetResult('/warehouse/requestscope/' . $this->workstation->scope['id'] . '/')
            ->getEntity();

        return Render::withHtml(
            $response,
            'page/overview.twig',
            array(
                'title' => 'Statistik',
                'workstation' => $this->workstation->getArrayCopy(),
                'department' => $this->department,
                'organisation' => $this->organisation,
                'waitingPeriod' => $waitingPeriod,
                'clientPeriod' => $clientPeriod,
                'requestPeriod' => $requestPeriod,
                'scopeId' => $this->workstation->scope['id'],
                'showAll' => 0
            )
        );
    }
}

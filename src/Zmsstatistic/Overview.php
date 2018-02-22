<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class Overview extends BaseController
{
    protected $resolveLevel = 3;

    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $waitingperiod = \App::$http
            ->readGetResult('/warehouse/waitingscope/' . $this->workstation->scope['id'] . '/')
            ->getEntity();
        $clientperiod = \App::$http
            ->readGetResult('/warehouse/clientscope/' . $this->workstation->scope['id'] . '/')
            ->getEntity();
        $requestperiod = \App::$http
            ->readGetResult('/warehouse/requestscope/' . $this->workstation->scope['id'] . '/')
            ->getEntity();

        if (!$this->workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/overview.twig',
            array(
                'title' => 'Statistik',
                'workstation' => $this->workstation->getArrayCopy(),
                'department' => $this->department,
                'organisation' => $this->organisation,
                'waitingperiod' => $waitingperiod,
                'clientperiod' => $clientperiod,
                'requestperiod' => $requestperiod,
                'scopeId' => $this->workstation->scope['id'],
                'showAll' => 0
            )
        );
    }
}

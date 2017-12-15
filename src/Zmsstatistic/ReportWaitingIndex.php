<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportWaitingIndex extends BaseController
{
    protected $totals = [
        'waitingcount',
        'waitingtime',
        'waitingtimecalculated'
    ];

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
        $department = \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity();
        $organisation = \App::$http->readGetResult('/department/' .$department->id . '/organisation/')->getEntity();

        $waitingPeriod = \App::$http
          ->readGetResult('/warehouse/waitingscope/' . $workstation->scope['id'] . '/')
          ->getEntity();

        $exchangeWaiting = null;
        if (isset($args['period'])) {
            $exchangeWaiting = \App::$http
            ->readGetResult('/warehouse/waitingscope/' . $workstation->scope['id'] . '/'. $args['period']. '/')
            ->getEntity()
            ->withCalculatedTotals($this->totals)
            ->toGrouped(['date', 'hour']);
        }

        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                  'error' => 'login_failed'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportWaitingIndex.twig',
            array(
              'title' => 'Wartestatistik Standort',
              'activeScope' => 'active',
              'menuActive' => 'waiting',
              'department' => $department,
              'organisation' => $organisation,
              'waitingPeriod' => $waitingPeriod,
              'showAll' => 1,
              'period' => $args['period'],
              'exchangeWaiting' => $exchangeWaiting,
              'source' => ['entity' => 'WaitingIndex'],
              'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}

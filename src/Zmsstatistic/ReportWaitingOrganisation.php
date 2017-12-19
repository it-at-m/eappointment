<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportWaitingOrganisation extends BaseController
{
    protected $hashset = [
        'waitingcount',
        'waitingtime',
        'waitingcalculated'
    ];

    protected $groupfields = [
        'date',
        'hour'
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
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                  'error' => 'login_failed'
                )
            );
        }

        $department = \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity();
        $organisation = \App::$http->readGetResult('/department/' . $department->id . '/organisation/')->getEntity();

        $waitingPeriod = \App::$http
          ->readGetResult('/warehouse/waitingorganisation/' . $organisation->id . '/')
          ->getEntity();

        $exchangeWaiting = null;
        if (isset($args['period'])) {
            $exchangeWaiting = \App::$http
            ->readGetResult('/warehouse/waitingorganisation/' . $organisation->id . '/'. $args['period']. '/')
            ->getEntity()
            ->toGrouped($this->groupfields, $this->hashset)
            ->withMaxByHour($this->hashset)
            ->withMaxAndAverageFromWaitingTime();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportWaitingIndex.twig',
            array(
              'title' => 'Wartestatistik Standort',
              'activeOrganisation' => 'active',
              'menuActive' => 'waiting',
              'department' => $department,
              'organisation' => $organisation,
              'waitingPeriod' => $waitingPeriod,
              'showAll' => 1,
              'period' => $args['period'],
              'exchangeWaiting' => $exchangeWaiting,
              'source' => ['entity' => 'WaitingOrganisation'],
              'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}

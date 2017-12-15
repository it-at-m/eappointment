<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportClientDepartment extends BaseController
{
    protected $totals = [
        'notificationscount',
        'notificationscost',
        'clientscount',
        'missed',
        'withappointment',
        'missedwithappointment',
        'requestscount'
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

        $clientPeriod = \App::$http
          ->readGetResult('/warehouse/clientdepartment/' . $department->id . '/')
          ->getEntity();

        $exchangeClient = null;
        $exchangeNotification = null;
        if (isset($args['period'])) {
            $exchangeClient = \App::$http
            ->readGetResult('/warehouse/clientdepartment/' . $department->id . '/'. $args['period']. '/')
            ->getEntity()
            ->withCalculatedTotals($this->totals)
            ->getJoinedHashData();

            $exchangeNotification = \App::$http
            ->readGetResult('/warehouse/notificationdepartment/' . $department->id . '/'. $args['period']. '/')
            ->getEntity()
            ->getJoinedHashData();
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
            'page/reportClientIndex.twig',
            array(
                'title' => 'Kundenstatistik Behörde',
                'activeDepartment' => 'active',
                'menuActive' => 'client',
                'department' => $department,
                'organisation' => $organisation,
                'clientperiod' => $clientPeriod,
                'showAll' => 1,
                'period' => $args['period'],
                'exchangeClient' => $exchangeClient,
                'exchangeNotification' => $exchangeNotification,
                'source' => ['entity' => 'ClientDepartment'],
                'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}

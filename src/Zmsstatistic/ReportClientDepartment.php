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
        $validator = $request->getAttribute('validator');
        $clientPeriod = \App::$http
          ->readGetResult('/warehouse/clientdepartment/' . $this->department->id . '/')
          ->getEntity();
        $exchangeClient = null;
        $exchangeNotification = null;
        if (isset($args['period'])) {
            $exchangeClient = \App::$http
            ->readGetResult('/warehouse/clientdepartment/' . $this->department->id . '/'. $args['period']. '/')
            ->getEntity()
            ->withCalculatedTotals($this->totals)
            ->toHashed();

            $exchangeNotification = \App::$http
            ->readGetResult('/warehouse/notificationdepartment/' . $this->department->id . '/'. $args['period']. '/')
            ->getEntity()
            ->toHashed();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'clientdepartment';
            $args['reports'][] = $exchangeNotification;
            $args['reports'][] = $exchangeClient;
            $args['department'] = $this->department;
            $args['organisation'] = $this->organisation;
            return (new Download\ClientReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportClientIndex.twig',
            array(
                'title' => 'Kundenstatistik Behörde',
                'activeDepartment' => 'active',
                'menuActive' => 'client',
                'department' => $this->department,
                'organisation' => $this->organisation,
                'clientperiod' => $clientPeriod,
                'showAll' => 1,
                'period' => $args['period'],
                'exchangeClient' => $exchangeClient,
                'exchangeNotification' => $exchangeNotification,
                'source' => ['entity' => 'ClientDepartment'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

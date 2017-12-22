<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportClientIndex extends BaseController
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
        $organisation = \App::$http->readGetResult('/department/' .$department->id . '/organisation/')->getEntity();

        $clientPeriod = \App::$http
          ->readGetResult('/warehouse/clientscope/' . $workstation->scope['id'] . '/')
          ->getEntity();

        $exchangeClient = null;
        $exchangeNotification = null;
        if (isset($args['period'])) {
            $exchangeClient = \App::$http
              ->readGetResult('/warehouse/clientscope/' . $workstation->scope['id'] . '/'. $args['period']. '/')
              ->getEntity()
              ->withCalculatedTotals($this->totals)
              ->toHashed();

            $exchangeNotification = \App::$http
              ->readGetResult('/warehouse/notificationscope/' . $workstation->scope['id'] . '/'. $args['period']. '/')
              ->getEntity()
              ->toHashed();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'clientscope';
            $args['reports'][] = $exchangeNotification;
            $args['reports'][] = $exchangeClient;
            $args['scope'] = $workstation->scope;
            $args['department'] = $department;
            $args['organisation'] = $organisation;
            return (new Download\ClientReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportClientIndex.twig',
            array(
                'title' => 'Kundenstatistik Standort',
                'activeScope' => 'active',
                'menuActive' => 'client',
                'department' => $department,
                'organisation' => $organisation,
                'clientperiod' => $clientPeriod,
                'showAll' => 1,
                'period' => $args['period'],
                'exchangeClient' => $exchangeClient,
                'exchangeNotification' => $exchangeNotification,
                'source' => ['entity' => 'ClientIndex'],
                'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}

<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportClientOrganisation extends BaseController
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
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $organisationId = $this->organisation->id;
        $clientPeriod = \App::$http
          ->readGetResult('/warehouse/clientorganisation/' . $organisationId . '/')
          ->getEntity();
        $exchangeClient = null;
        $exchangeNotification = null;
        if (isset($args['period'])) {
            $exchangeClient = \App::$http
            ->readGetResult('/warehouse/clientorganisation/' . $organisationId . '/'. $args['period']. '/')
            ->getEntity()
            ->withCalculatedTotals($this->totals, 'date')
            ->toHashed();

            $exchangeNotification = \App::$http
            ->readGetResult(
                '/warehouse/notificationorganisation/' . $organisationId . '/'. $args['period']. '/',
                ['groupby' => 'month']
            )
            ->getEntity()
            ->toHashed();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'clientorganisation';
            $args['reports'][] = $exchangeNotification;
            $args['reports'][] = $exchangeClient;
            $args['organisation'] = $this->organisation;
            return (new Download\ClientReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return Render::withHtml(
            $response,
            'page/reportClientIndex.twig',
            array(
                'title' => 'Kundenstatistik Bezirk',
                'activeOrganisation' => 'active',
                'menuActive' => 'client',
                'department' => $this->department,
                'organisation' => $this->organisation,
                'clientperiod' => $clientPeriod,
                'showAll' => 1,
                'period' => isset($args['period']) ? $args['period'] : null,
                'exchangeClient' => $exchangeClient,
                'exchangeNotification' => $exchangeNotification,
                'source' => ['entity' => 'ClientOrganisation'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

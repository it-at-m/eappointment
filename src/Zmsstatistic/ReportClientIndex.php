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
        $scopeId = $this->workstation->scope['id'];
        $clientPeriod = \App::$http
          ->readGetResult('/warehouse/clientscope/' . $scopeId . '/')
          ->getEntity();

        $exchangeClient = null;
        $exchangeNotification = null;
        if (isset($args['period'])) {
            try {
                $exchangeClient = \App::$http
                    ->readGetResult('/warehouse/clientscope/' . $scopeId . '/'. $args['period']. '/')
                    ->getEntity()
                    ->withCalculatedTotals($this->totals, 'date')
                    ->toHashed();
            } catch (\Exception $exception) {
                // do nothing
            }
            try {
                $exchangeNotification = \App::$http
                    ->readGetResult(
                        '/warehouse/notificationscope/' . $scopeId . '/'. $args['period']. '/',
                        ['groupby' => 'month']
                    )
                    ->getEntity()
                    ->toHashed();
            } catch (\Exception $exception) {
                // do nothing
            }
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'clientscope';
            if (count($exchangeNotification->data)) {
                $args['reports'][] = $exchangeNotification;
            }
            if (count($exchangeClient->data)) {
                $args['reports'][] = $exchangeClient;
            }
            $args['scope'] = $this->workstation->getScope();
            $args['department'] = $this->department;
            $args['organisation'] = $this->organisation;
            return (new Download\ClientReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportClientIndex.twig',
            array(
                'title' => 'Kundenstatistik Standort',
                'activeScope' => 'active',
                'menuActive' => 'client',
                'department' => $this->department,
                'organisation' => $this->organisation,
                'clientperiod' => $clientPeriod,
                'showAll' => 1,
                'period' => isset($args['period']) ? $args['period'] : null,
                'exchangeClient' => $exchangeClient,
                'exchangeNotification' => $exchangeNotification,
                'source' => ['entity' => 'ClientIndex'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

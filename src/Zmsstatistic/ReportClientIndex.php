<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportClientIndex extends BaseController
{
    protected $totals = [
        'clientscount',
        'missed',
        'withappointment',
        'missedwithappointment',
        'noappointment',
        'missednoappointment',
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
        $scopeId = $this->workstation->scope['id'];
        $clientPeriod = \App::$http
          ->readGetResult('/warehouse/clientscope/' . $scopeId . '/')
          ->getEntity();

        $exchangeClient = null;
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
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'clientscope';

            if (count($exchangeClient->data)) {
                $args['reports'][] = $exchangeClient;
            }
            $args['scope'] = $this->workstation->getScope();
            $args['department'] = $this->department;
            $args['organisation'] = $this->organisation;
            return (new Download\ClientReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return Render::withHtml(
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
                'source' => ['entity' => 'ClientIndex'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

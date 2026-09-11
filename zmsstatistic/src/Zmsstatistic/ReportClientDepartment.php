<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportClientDepartment extends BaseController
{
    protected array $totals = [
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
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): mixed {
        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        $validator = $request->getAttribute('validator');
        $clientPeriod = \App::http()
          ->readGetResult('/warehouse/clientdepartment/' . $this->department->id . '/')
          ->getEntity();

        $exchangeClient = null;
        if (isset($args['period'])) {
            /** @var mixed $entity */
            $entity = \App::http()
                ->readGetResult('/warehouse/clientdepartment/' . $this->department->id . '/' . $args['period'] . '/')
                ->getEntity();
            $exchangeClient = $entity
                ->withCalculatedTotals($this->totals, 'date')
                ->toHashed();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'clientdepartment';
            $args['reports'][] = $exchangeClient;
            $args['department'] = $this->department;
            $args['organisation'] = $this->organisation;

            /** @var mixed $container */
            $container = \App::$slim->getContainer();
            return (new Download\ClientReport($container))->readResponse($request, $response, $args);
        }

        return Render::withHtml(
            $response,
            'page/reportClientIndex.twig',
            array(
                'title' => 'Kundenstatistik Behörde',
                'activeDepartment' => 'active',
                'menuActive' => 'client',
                'department' => $this->department,
                'organisation' => $this->organisation,
                'clientPeriod' => $clientPeriod,
                'showAll' => 1,
                'period' => isset($args['period']) ? $args['period'] : null,
                'exchangeClient' => $exchangeClient,
                'source' => ['entity' => 'ClientDepartment'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

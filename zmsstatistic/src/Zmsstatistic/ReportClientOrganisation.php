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
    protected array $totals = [
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
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        $validator = $request->getAttribute('validator');
        $organisationId = $this->organisation->id;
        $clientPeriod = \App::http()
          ->readGetResult('/warehouse/clientorganisation/' . $organisationId . '/')
          ->getEntity();
        $exchangeClient = null;
        if (isset($args['period'])) {
            /** @var mixed $entity */
            $entity = \App::http()
            ->readGetResult('/warehouse/clientorganisation/' . $organisationId . '/' . $args['period'] . '/')
            ->getEntity();
            $exchangeClient = $entity
            ->withCalculatedTotals($this->totals, 'date')
            ->toHashed();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'clientorganisation';
            $args['reports'][] = $exchangeClient;
            $args['organisation'] = $this->organisation;
            /** @var mixed $container */
            $container = \App::$slim->getContainer();
            return (new Download\ClientReport($container))->readResponse($request, $response, $args);
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
                'clientPeriod' => $clientPeriod,
                'showAll' => 1,
                'period' => isset($args['period']) ? $args['period'] : null,
                'exchangeClient' => $exchangeClient,
                'source' => ['entity' => 'ClientOrganisation'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

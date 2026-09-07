<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportRequestDepartment extends BaseController
{
    protected array $hashset = [
        'requestscount'
    ];

    protected array $groupfields = [
        'name',
        'date'
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
        $requestPeriod = \App::http()
          ->readGetResult('/warehouse/requestdepartment/' . $this->department->id . '/')
          ->getEntity();
        $exchangeRequest = null;
        if (isset($args['period'])) {
            /** @var mixed $entity */
            $entity = \App::http()
            ->readGetResult('/warehouse/requestdepartment/' . $this->department->id . '/' . $args['period'] . '/')
            ->getEntity();
            $exchangeRequest = $entity
            ->toGrouped($this->groupfields, $this->hashset)
            ->withRequestsSum()
            ->withAverage('processingtime')
            ->withWeightedAverageProcessingTime()
            ->withUncapturedRequestRowSortedLast();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'requestdepartment';
            $args['reports'][] = $exchangeRequest;
            $args['scope'] = $this->workstation->scope;
            $args['department'] = $this->department;
            $args['organisation'] = $this->organisation;
            /** @var mixed $container */
            $container = \App::$slim->getContainer();
            return (new Download\RequestReport($container))->readResponse($request, $response, $args);
        }

        return Render::withHtml(
            $response,
            'page/reportRequestIndex.twig',
            array(
              'title' => 'Dienstleistungsstatistik Behörde',
              'activeDepartment' => 'active',
              'menuActive' => 'request',
              'department' => $this->department,
              'organisation' => $this->organisation,
              'owner' => $this->owner,
              'requestPeriod' => $requestPeriod,
              'showAll' => 1,
              'period' => (isset($args['period'])) ? $args['period'] : null,
              'exchangeRequest' => $exchangeRequest,
              'source' => ['entity' => 'RequestDepartment'],
              'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

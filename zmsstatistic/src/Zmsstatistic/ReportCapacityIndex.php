<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsentities\Exchange;
use BO\Zmsstatistic\Helper\ReportHelper;
use BO\Zmsstatistic\Service\ReportCapacityService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportCapacityIndex extends BaseController
{
    protected $resolveLevel = 2;

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
        $validator = $request->getAttribute('validator');
        $reportCapacityService = new ReportCapacityService();
        $reportHelper = new ReportHelper();

        $selectedScopes = $reportHelper->extractSelectedScopes(
            $validator->getParameter('scopes')->isArray()->getValue() ?? []
        );

        $scopeId = !empty($selectedScopes) ? implode(',', $selectedScopes) : $this->workstation->scope['id'];

        $capacityPeriod = $reportCapacityService->getCapacityPeriod($this->workstation->scope['id']);
        $scopeDateBounds = $reportCapacityService->getScopeDateBoundsByScopeId();

        $dateRange = $reportHelper->extractDateRange(
            $validator->getParameter('from')->isString()->getValue(),
            $validator->getParameter('to')->isString()->getValue()
        );

        $exchangeCapacity = $reportCapacityService->getExchangeCapacityData($scopeId, $dateRange, $args);
        $exchangeCapacityChart = null;

        if ($exchangeCapacity instanceof Exchange) {
            $exchangeCapacityChart = $reportCapacityService->buildChartExchange(
                $exchangeCapacity,
                $dateRange,
                $args['period'] ?? null
            );
        }

        return $this->renderHtmlResponse(
            $response,
            $args,
            $capacityPeriod,
            $dateRange,
            $exchangeCapacity,
            $exchangeCapacityChart,
            $selectedScopes,
            $scopeDateBounds
        );
    }

    private function renderHtmlResponse(
        $response,
        $args,
        $capacityPeriod,
        $dateRange,
        $exchangeCapacity,
        $exchangeCapacityChart,
        $selectedScopes = [],
        array $scopeDateBounds = []
    ): ResponseInterface {
        return Render::withHtml(
            $response,
            'page/reportCapacityIndex.twig',
            [
                'title' => 'Terminkapazität Standort',
                'activeScope' => 'active',
                'menuActive' => 'capacity',
                'department' => $this->department,
                'organisation' => $this->organisation,
                'capacityPeriod' => $capacityPeriod,
                'scopeDateBounds' => $scopeDateBounds,
                'showAll' => 1,
                'period' => $args['period'] ?? null,
                'dateRange' => $dateRange,
                'exchangeCapacity' => $exchangeCapacity,
                'exchangeCapacityChart' => $exchangeCapacityChart,
                'source' => ['entity' => 'CapacityIndex'],
                'selectedScopeIds' => $selectedScopes,
                'workstation' => $this->workstation->getArrayCopy(),
            ]
        );
    }
}

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
        $this->workstation->getUseraccount()->testPermissions(['statistic', 'capacityreport']);

        $validator = $request->getAttribute('validator');
        $reportCapacityService = new ReportCapacityService();
        $reportHelper = new ReportHelper();

        $selectedScopes = $reportHelper->extractSelectedScopes(
            $validator->getParameter('scopes')->isArray()->getValue() ?? []
        );

        $workstationScopeId = $reportHelper->getWorkstationScopeId($this->workstation);
        $scopeId = $reportHelper->resolveScopeIdParam($selectedScopes, $workstationScopeId);

        $capacityPeriod = $workstationScopeId !== null
            ? $reportCapacityService->getCapacityPeriod((string) $workstationScopeId)
            : null;
        $scopeDateBounds = $reportCapacityService->getScopeDateBoundsByScopeId();

        $dateRange = $reportHelper->extractDateRange(
            $validator->getParameter('from')->isString()->getValue(),
            $validator->getParameter('to')->isString()->getValue()
        );

        $exchangeCapacity = $reportCapacityService->getExchangeCapacityData($scopeId, $dateRange, $args);
        $exchangeCapacityChart = null;
        $exchangeCapacityChartSparse = null;

        if ($exchangeCapacity instanceof Exchange) {
            $period = $args['period'] ?? null;
            $exchangeCapacityChartSparse = $reportCapacityService->buildSparseChartExchange(
                $exchangeCapacity,
                $dateRange,
                $period
            );
            $exchangeCapacityChart = $reportCapacityService->buildChartExchange(
                $exchangeCapacity,
                $dateRange,
                $period
            );
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            return $this->handleDownloadRequest(
                $request,
                $response,
                $args,
                $scopeId,
                $exchangeCapacity,
                $dateRange,
                $selectedScopes,
                $reportCapacityService
            );
        }

        $displayScopeIds = $selectedScopes;
        if ($displayScopeIds === [] && $workstationScopeId !== null) {
            $displayScopeIds = [(string) $workstationScopeId];
        }
        $scopeSlotTimeHint = $reportCapacityService->formatScopeSlotTimeHint(
            $reportCapacityService->getSelectedScopeSlotTimes($displayScopeIds)
        );

        return $this->renderHtmlResponse(
            $response,
            $args,
            $capacityPeriod,
            $dateRange,
            $exchangeCapacity,
            $exchangeCapacityChart,
            $exchangeCapacityChartSparse,
            $selectedScopes,
            $scopeDateBounds,
            $scopeSlotTimeHint
        );
    }

    private function handleDownloadRequest(
        RequestInterface $request,
        ResponseInterface $response,
        array $args,
        string $scopeId,
        mixed $exchangeCapacity,
        ?array $dateRange,
        array $selectedScopes = [],
        ?ReportCapacityService $reportCapacityService = null
    ): ResponseInterface {
        if ($reportCapacityService === null) {
            $reportCapacityService = new ReportCapacityService();
        }

        $validator = $request->getAttribute('validator');
        $valueMode = $validator->getParameter('valueMode')->isString()->getValue();
        $valueMode = $valueMode === 'minutes' ? 'minutes' : 'slots';
        $channelMode = $validator->getParameter('channelMode')->isString()->getValue();
        $channelMode = in_array($channelMode, ['total', 'public', 'intern_only'], true)
            ? $channelMode
            : 'total';

        $args = $reportCapacityService->prepareDownloadArgs(
            $args,
            $scopeId,
            $exchangeCapacity,
            $dateRange,
            $selectedScopes,
            $valueMode,
            $channelMode
        );

        return (new Download\CapacityReport(\App::$slim->getContainer()))
            ->readResponse($request, $response, $args);
    }

    private function renderHtmlResponse(
        $response,
        $args,
        $capacityPeriod,
        $dateRange,
        $exchangeCapacity,
        $exchangeCapacityChart,
        $exchangeCapacityChartSparse,
        $selectedScopes = [],
        array $scopeDateBounds = [],
        ?string $scopeSlotTimeHint = null
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
                'exchangeCapacityChartSparse' => $exchangeCapacityChartSparse,
                'source' => ['entity' => 'CapacityIndex'],
                'selectedScopeIds' => $selectedScopes,
                'scopeSlotTimeHint' => $scopeSlotTimeHint,
                'workstation' => $this->workstation->getArrayCopy(),
            ]
        );
    }
}

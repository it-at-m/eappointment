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
    protected int $resolveLevel = 2;

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
        $this->workstation->getUseraccount()->testPermissions(['statistic', 'capacityreport']);

        /** @var \Psr\Http\Message\ServerRequestInterface $request */
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

        $fetchedCapacity = $reportCapacityService->getExchangeCapacityData($scopeId, $dateRange, $args);
        $exchangeCapacityDaily = null;
        $exchangeCapacityChartDaily = null;
        $exchangeCapacityChartSparseDaily = null;
        $exchangeCapacityHourly = null;
        $exchangeCapacityChartHourly = null;
        $exchangeCapacityChartSparseHourly = null;
        $displayExchanges = null;

        if ($fetchedCapacity instanceof Exchange) {
            $period = $args['period'] ?? null;
            $displayExchanges = $reportCapacityService->buildCapacityDisplayExchanges(
                $fetchedCapacity,
                $dateRange,
                $period
            );
            $exchangeCapacityDaily = $displayExchanges['dailyTable'];
            $exchangeCapacityChartSparseDaily = $displayExchanges['dailyChartSparse'];
            $exchangeCapacityChartDaily = $displayExchanges['dailyChartFull'];
            $exchangeCapacityHourly = $displayExchanges['hourlyTable'];
            $exchangeCapacityChartSparseHourly = $displayExchanges['hourlyChartSparse'];
            $exchangeCapacityChartHourly = $displayExchanges['hourlyChartFull'];
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $granularity = $validator->getParameter('granularity')->isString()->getValue();
            $timeline = $validator->getParameter('timeline')->isString()->getValue();
            $downloadExchange = is_array($displayExchanges)
                ? $reportCapacityService->selectDownloadExchange(
                    $displayExchanges,
                    $granularity,
                    $timeline
                )
                : null;

            return $this->handleDownloadRequest(
                $request,
                $response,
                $args,
                $scopeId,
                $downloadExchange,
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
            $exchangeCapacityDaily,
            $exchangeCapacityChartDaily,
            $exchangeCapacityChartSparseDaily,
            $selectedScopes,
            $scopeDateBounds,
            $scopeSlotTimeHint,
            $exchangeCapacityHourly,
            $exchangeCapacityChartHourly,
            $exchangeCapacityChartSparseHourly
        );
    }

    private function handleDownloadRequest(
        RequestInterface $request,
        ResponseInterface $response,
        array $args,
        string $scopeId,
        mixed $downloadExchange,
        ?array $dateRange,
        array $selectedScopes = [],
        ?ReportCapacityService $reportCapacityService = null
    ): ResponseInterface {
        if ($reportCapacityService === null) {
            $reportCapacityService = new ReportCapacityService();
        }

        /** @var \Psr\Http\Message\ServerRequestInterface $request */
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
            $downloadExchange,
            $dateRange,
            $selectedScopes,
            $valueMode,
            $channelMode
        );

        /** @var mixed $container */
        $container = \App::$slim->getContainer();
        return (new Download\CapacityReport($container))
            ->readResponse($request, $response, $args);
    }

    private function renderHtmlResponse(
        ResponseInterface $response,
        array $args,
        mixed $capacityPeriod,
        array|null $dateRange,
        mixed $exchangeCapacityDaily,
        Exchange|null $exchangeCapacityChartDaily,
        Exchange|null $exchangeCapacityChartSparseDaily,
        array $selectedScopes = [],
        array $scopeDateBounds = [],
        ?string $scopeSlotTimeHint = null,
        Exchange|null $exchangeCapacityHourly = null,
        Exchange|null $exchangeCapacityChartHourly = null,
        Exchange|null $exchangeCapacityChartSparseHourly = null
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
                'exchangeCapacityDaily' => $exchangeCapacityDaily,
                'exchangeCapacityChartDaily' => $exchangeCapacityChartDaily,
                'exchangeCapacityChartSparseDaily' => $exchangeCapacityChartSparseDaily,
                'exchangeCapacityHourly' => $exchangeCapacityHourly,
                'exchangeCapacityChartHourly' => $exchangeCapacityChartHourly,
                'exchangeCapacityChartSparseHourly' => $exchangeCapacityChartSparseHourly,
                'source' => ['entity' => 'CapacityIndex'],
                'selectedScopeIds' => $selectedScopes,
                'scopeSlotTimeHint' => $scopeSlotTimeHint,
                'workstation' => $this->workstation->getArrayCopy(),
            ]
        );
    }
}

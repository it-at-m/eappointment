<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsstatistic\Helper\ReportHelper;
use BO\Zmsstatistic\Service\ReportWaitingService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportWaitingIndex extends BaseController
{
    protected $resolveLevel = 3;

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
        $reportWaitingService = new ReportWaitingService();
        $reportHelper = new ReportHelper();

        $selectedScopes = $reportHelper->extractSelectedScopes(
            $validator->getParameter('scopes')->isArray()->getValue() ?? []
        );

        $scopeIds = !empty($selectedScopes) ? implode(',', $selectedScopes) : $this->workstation->scope['id'];

        $waitingPeriod = $reportWaitingService->getWaitingPeriod($this->workstation->scope['id']);
        
        $dateRange = $reportHelper->extractDateRange(
            $validator->getParameter('from')->isString()->getValue(),
            $validator->getParameter('to')->isString()->getValue()
        );

        $exchangeWaiting = $reportWaitingService->getExchangeWaitingData($scopeIds, $dateRange, $args);

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            return $this->handleDownloadRequest(
                $request,
                $response,
                $args,
                $exchangeWaiting,
                $dateRange,
                $selectedScopes,
                $reportWaitingService
            );
        }

        return $this->renderHtmlResponse(
            $response,
            $args,
            $waitingPeriod,
            $dateRange,
            $exchangeWaiting,
            $selectedScopes
        );
    }

    /**
     * Handle download request and return Excel file
     */
    private function handleDownloadRequest(
        $request,
        $response,
        $args,
        $exchangeWaiting,
        $dateRange,
        $selectedScopes = [],
        $reportWaitingService = null
    ): ResponseInterface {
        if ($reportWaitingService === null) {
            $reportWaitingService = new ReportWaitingService();
        }

        $args = $reportWaitingService->prepareDownloadArgs($args, $exchangeWaiting, $dateRange, $selectedScopes);

        $args['scope'] = $this->workstation->getScope();
        $args['department'] = $this->department;
        $args['organisation'] = $this->organisation;

        return (new Download\WaitingReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
    }

    /**
     * Render HTML response for the waiting report page
     */
    private function renderHtmlResponse(
        $response,
        $args,
        $waitingPeriod,
        $dateRange,
        $exchangeWaiting,
        $selectedScopes = []
    ): ResponseInterface {
        return Render::withHtml(
            $response,
            'page/reportWaitingIndex.twig',
            [
                'title' => 'Wartestatistik Standort',
                'activeScope' => 'active',
                'menuActive' => 'waiting',
                'department' => $this->department,
                'organisation' => $this->organisation,
                'waitingPeriod' => $waitingPeriod,
                'showAll' => 1,
                'period' => isset($args['period']) ? $args['period'] : null,
                'dateRange' => $dateRange,
                'exchangeWaiting' => $exchangeWaiting,
                'source' => ['entity' => 'WaitingIndex'],
                'selectedScopeIds' => $selectedScopes,
                'workstation' => $this->workstation->getArrayCopy()
            ]
        );
    }
}

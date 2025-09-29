<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsstatistic\Helper\ReportHelper;
use BO\Zmsstatistic\Service\ReportRequestService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportRequestIndex extends BaseController
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
        $reportRequestService = new ReportRequestService();
        $reportHelper = new ReportHelper();

        $selectedScopes = $reportHelper->extractSelectedScopes(
            $validator->getParameter('scopes')->isArray()->getValue() ?? []
        );

        $scopeIds = !empty($selectedScopes) ? implode(',', $selectedScopes) : $this->workstation->scope['id'];

        $requestPeriod = $reportRequestService->getRequestPeriod($this->workstation->scope['id']);

        $dateRange = $reportHelper->extractDateRange(
            $validator->getParameter('from')->isString()->getValue(),
            $validator->getParameter('to')->isString()->getValue()
        );

        $exchangeRequest = $reportRequestService->getExchangeRequestData($scopeIds, $dateRange, $args);

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            return $this->handleDownloadRequest(
                $request,
                $response,
                $args,
                $exchangeRequest,
                $dateRange,
                $selectedScopes,
                $reportRequestService
            );
        }

        return $this->renderHtmlResponse(
            $response,
            $args,
            $requestPeriod,
            $dateRange,
            $exchangeRequest,
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
        $exchangeRequest,
        $dateRange,
        $selectedScopes = [],
        $reportRequestService = null
    ): ResponseInterface {
        if ($reportRequestService === null) {
            $reportRequestService = new ReportRequestService();
        }

        $args = $reportRequestService->prepareDownloadArgs($args, $exchangeRequest, $dateRange, $selectedScopes);

        $args['scope'] = $this->workstation->getScope();
        $args['department'] = $this->department;
        $args['organisation'] = $this->organisation;

        return (new Download\RequestReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
    }

    /**
     * Render HTML response for the report page
     */
    private function renderHtmlResponse(
        $response,
        $args,
        $requestPeriod,
        $dateRange,
        $exchangeRequest,
        $selectedScopes = []
    ): ResponseInterface {
        return Render::withHtml(
            $response,
            'page/reportRequestIndex.twig',
            array(
                'title' => 'Kundenstatistik Standort',
                'activeScope' => 'active',
                'menuActive' => 'request',
                'department' => $this->department,
                'organisation' => $this->organisation,
                'requestPeriod' => $requestPeriod,
                'showAll' => 1,
                'period' => isset($args['period']) ? $args['period'] : null,
                'dateRange' => $dateRange,
                'exchangeRequest' => $exchangeRequest,
                'source' => ['entity' => 'RequestIndex'],
                'selectedScopeIds' => $selectedScopes,
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

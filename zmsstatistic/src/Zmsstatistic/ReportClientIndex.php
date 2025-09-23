<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsstatistic\Service\ReportClientService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportClientIndex extends BaseController
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
        $reportClientService = new ReportClientService();

        $selectedScopes = $reportClientService->extractSelectedScopes(
            $validator->getParameter('scopes')->isArray()->getValue() ?? []
        );

        $scopeId = !empty($selectedScopes) ? implode(',', $selectedScopes) : $this->workstation->scope['id'];

        $clientPeriod = $reportClientService->getClientPeriod($this->workstation->scope['id']);

        $dateRange = $reportClientService->extractDateRange(
            $validator->getParameter('from')->isString()->getValue(),
            $validator->getParameter('to')->isString()->getValue()
        );

        $exchangeClient = $reportClientService->getExchangeClientData($scopeId, $dateRange, $args);

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            return $this->handleDownloadRequest(
                $request,
                $response,
                $args,
                $exchangeClient,
                $dateRange,
                $selectedScopes,
                $reportClientService
            );
        }

        return $this->renderHtmlResponse($response, $args, $clientPeriod, $dateRange, $exchangeClient, $selectedScopes);
    }

    /**
     * Handle download request and return Excel file
     */
    private function handleDownloadRequest(
        $request,
        $response,
        $args,
        $exchangeClient,
        $dateRange,
        $selectedScopes = [],
        $reportClientService = null
    ): ResponseInterface {
        if ($reportClientService === null) {
            $reportClientService = new ReportClientService();
        }

        $args = $reportClientService->prepareDownloadArgs($args, $exchangeClient, $dateRange, $selectedScopes);

        $args['scope'] = $this->workstation->getScope();
        $args['department'] = $this->department;
        $args['organisation'] = $this->organisation;

        return (new Download\ClientReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
    }

    /**
     * Render HTML response for the report page
     */
    private function renderHtmlResponse(
        $response,
        $args,
        $clientPeriod,
        $dateRange,
        $exchangeClient,
        $selectedScopes = []
    ): ResponseInterface {
        return Render::withHtml(
            $response,
            'page/reportClientIndex.twig',
            array(
                'title' => 'Kundenstatistik Standort',
                'activeScope' => 'active',
                'menuActive' => 'client',
                'department' => $this->department,
                'organisation' => $this->organisation,
                'clientPeriod' => $clientPeriod,
                'showAll' => 1,
                'period' => isset($args['period']) ? $args['period'] : null,
                'dateRange' => $dateRange,
                'exchangeClient' => $exchangeClient,
                'source' => ['entity' => 'ClientIndex'],
                'selectedScopeIds' => $selectedScopes,
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}

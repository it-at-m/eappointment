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

        // Extract date parameters and validate
        $dateRange = $this->extractDateRange($validator);
        
        // Get exchange client data based on date range or period
        $exchangeClient = $this->getExchangeClientData($validator, $scopeId, $dateRange, $args);

        // Handle download request
        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            return $this->handleDownloadRequest($request, $response, $args, $exchangeClient, $dateRange);
        }

        // Render HTML response
        return $this->renderHtmlResponse($response, $args, $clientPeriod, $dateRange, $exchangeClient);
    }

    /**
     * Extract and validate date range from request parameters
     */
    private function extractDateRange($validator): ?array
    {
        $fromDate = $validator->getParameter('from')->isString()->getValue();
        $toDate = $validator->getParameter('to')->isString()->getValue();
        
        if ($fromDate && $toDate && $this->isValidDateFormat($fromDate) && $this->isValidDateFormat($toDate)) {
            return [
                'from' => $fromDate,
                'to' => $toDate
            ];
        }
        
        return null;
    }

    /**
     * Get exchange client data based on date range or period
     */
    private function getExchangeClientData($validator, $scopeId, $dateRange, $args): mixed
    {
        if ($dateRange) {
            return $this->getExchangeClientForDateRange($scopeId, $dateRange);
        } elseif (isset($args['period'])) {
            return $this->getExchangeClientForPeriod($scopeId, $args['period']);
        }
        
        return null;
    }

    /**
     * Get exchange client data for a specific date range
     */
    private function getExchangeClientForDateRange($scopeId, $dateRange): mixed
    {
        $fromDate = $dateRange['from'];
        $toDate = $dateRange['to'];
        
        try {
            // Get all years that need to be fetched for this date range
            $years = $this->getYearsForDateRange($fromDate, $toDate);
            
            error_log("Date range spans years: " . implode(', ', $years));
            error_log("Filtering from: " . $fromDate . " to: " . $toDate);
            
            // Fetch and combine data from all necessary years
            $combinedData = $this->fetchAndCombineDataFromYears($scopeId, $years);
            
            if (empty($combinedData['data'])) {
                error_log("No data found for years: " . implode(', ', $years));
                return null;
            }
            
            error_log("Total data rows before filtering: " . count($combinedData['data']));

            // Filter data by date range
            $filteredData = $this->filterDataByDateRange($combinedData['data'], $fromDate, $toDate);

            error_log("Total data rows after filtering: " . count($filteredData));

            // Create filtered exchange client
            return $this->createFilteredExchangeClient($combinedData['entity'], $filteredData, $fromDate, $toDate);

        } catch (\Exception $exception) {
            error_log("Exception in getExchangeClientForDateRange: " . $exception->getMessage());
            return null;
        }
    }

    /**
     * Get exchange client data for a specific period (legacy functionality)
     */
    private function getExchangeClientForPeriod($scopeId, $period): mixed
    {
        try {
            return \App::$http
                ->readGetResult('/warehouse/clientscope/' . $scopeId . '/' . $period . '/')
                ->getEntity()
                ->withCalculatedTotals($this->totals, 'date')
                ->toHashed();
        } catch (\Exception $exception) {
            error_log("Exception in getExchangeClientForPeriod: " . $exception->getMessage());
            return null;
        }
    }

    /**
     * Get all years that need to be fetched for a date range
     */
    private function getYearsForDateRange($fromDate, $toDate): array
    {
        $fromYear = (int) substr($fromDate, 0, 4);
        $toYear = (int) substr($toDate, 0, 4);
        
        $years = [];
        for ($year = $fromYear; $year <= $toYear; $year++) {
            $years[] = $year;
        }
        
        return $years;
    }

    /**
     * Fetch and combine data from multiple years
     */
    private function fetchAndCombineDataFromYears($scopeId, $years): array
    {
        $combinedData = [];
        $baseEntity = null;
        
        foreach ($years as $year) {
            try {
                $exchangeClient = \App::$http
                    ->readGetResult('/warehouse/clientscope/' . $scopeId . '/' . $year . '/', ['groupby' => 'day'])
                    ->getEntity();
                
                // Use the first successfully fetched entity as the base
                if ($baseEntity === null) {
                    $baseEntity = $exchangeClient;
                }
                
                // Combine data from all years
                if (isset($exchangeClient->data)) {
                    $combinedData = array_merge($combinedData, $exchangeClient->data);
                }
                
                error_log("Fetched " . count($exchangeClient->data) . " rows for year " . $year);
                
            } catch (\Exception $exception) {
                error_log("Failed to fetch data for year " . $year . ": " . $exception->getMessage());
                // Continue with other years - don't fail completely if one year is missing
            }
        }
        
        return [
            'entity' => $baseEntity,
            'data' => $combinedData
        ];
    }

    /**
     * Filter data array by date range
     */
    private function filterDataByDateRange($data, $fromDate, $toDate): array
    {
        $filteredData = [];
        foreach ($data as $row) {
            if ($row[1] >= $fromDate && $row[1] <= $toDate) {
                $filteredData[] = $row;
            }
        }
        return $filteredData;
    }

    /**
     * Create filtered exchange client with updated properties
     */
    private function createFilteredExchangeClient($exchangeClientFull, $filteredData, $fromDate, $toDate): mixed
    {
        // Clone entity and replace data
        $exchangeClient = clone $exchangeClientFull;
        $exchangeClient->data = $filteredData;
        
        // Ensure period is set for download functionality
        if (!isset($exchangeClient->period)) {
            $exchangeClient->period = 'day';
        }
        
        // Update firstDay and lastDay to reflect the actual filtered date range
        $exchangeClient->firstDay = (new \BO\Zmsentities\Day())->setDateTime(new \DateTime($fromDate));
        $exchangeClient->lastDay = (new \BO\Zmsentities\Day())->setDateTime(new \DateTime($toDate));
        
        return $exchangeClient
            ->withCalculatedTotals($this->totals, 'date')
            ->toHashed();
    }

    /**
     * Handle download request and return Excel file
     */
    private function handleDownloadRequest($request, $response, $args, $exchangeClient, $dateRange): ResponseInterface
    {
        $args['category'] = 'clientscope';

        // Set period for download filename - use date range or existing period
        if ($dateRange) {
            $args['period'] = $dateRange['from'] . '_' . $dateRange['to'];
        }

        if ($exchangeClient && count($exchangeClient->data)) {
            $args['reports'][] = $exchangeClient;
            error_log("Adding report to download with " . count($exchangeClient->data) . " data rows");
            error_log("Report period: " . ($exchangeClient->period ?? 'not set'));
        } else {
            error_log("No exchangeClient data for download. ExchangeClient: " . ($exchangeClient ? 'exists' : 'null') . ", Data count: " . ($exchangeClient ? count($exchangeClient->data) : 'N/A'));
        }

        $args['scope'] = $this->workstation->getScope();
        $args['department'] = $this->department;
        $args['organisation'] = $this->organisation;
        
        return (new Download\ClientReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
    }

    /**
     * Render HTML response for the report page
     */
    private function renderHtmlResponse($response, $args, $clientPeriod, $dateRange, $exchangeClient): ResponseInterface
    {
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
                'dateRange' => $dateRange,
                'exchangeClient' => $exchangeClient,
                'source' => ['entity' => 'ClientIndex'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }

    /**
     * Validate if the given string is a valid date format (YYYY-MM-DD)
     */
    private function isValidDateFormat($date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }
}

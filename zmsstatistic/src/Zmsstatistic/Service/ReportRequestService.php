<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Service;

use BO\Zmsentities\Day;
use BO\Zmsstatistic\Helper\ReportHelper;
use DateTime;
use Exception;

class ReportRequestService
{
    protected $totals = ['requestscount'];

    protected $hashset = [
        'requestscount'
    ];

    protected $groupfields = [
        'name',
        'date'
    ];

    /**
     * Get exchange request data based on date range or period
     */
    public function getExchangeRequestData(string $scopeId, ?array $dateRange, array $args): mixed
    {
        if ($dateRange) {
            return $this->getExchangeRequestForDateRange($scopeId, $dateRange);
        }

        return isset($args['period'])
            ? $this->getExchangeRequestForPeriod($scopeId, $args['period'])
            : null;
    }

    /**
     * Get exchange request data for a specific date range
     */
    public function getExchangeRequestForDateRange(string $scopeId, array $dateRange): mixed
    {
        if (!isset($dateRange['from']) || !isset($dateRange['to'])) {
            return null;
        }
        $fromDate = $dateRange['from'];
        $toDate = $dateRange['to'];

        try {
            $reportHelper = new ReportHelper();
            $years = $reportHelper->getYearsForDateRange($fromDate, $toDate);
            $combinedData = $this->fetchAndCombineDataFromYears($scopeId, $years);

            if (empty($combinedData['data'])) {
                return null;
            }

            $filteredData = $this->filterDataByDateRange($combinedData['data'], $fromDate, $toDate);

            if (empty($filteredData)) {
                return null;
            }

            return $this->createFilteredExchangeRequest($combinedData['entity'], $filteredData, $fromDate, $toDate);
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Get exchange request data for a specific period (legacy functionality)
     */
    public function getExchangeRequestForPeriod(string $scopeId, string $period): mixed
    {
        try {
            return \App::$http
                ->readGetResult('/warehouse/requestscope/' . $scopeId . '/' . $period . '/')
                ->getEntity()
                ->toGrouped($this->groupfields, $this->hashset)
                ->withRequestsSum()
                ->withAverage('processingtime');
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Get request period data for the current scope
     */
    public function getRequestPeriod(string $scopeId): mixed
    {
        try {
            return \App::$http
                ->readGetResult('/warehouse/requestscope/' . $scopeId . '/')
                ->getEntity();
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Fetch and combine data from multiple years
     */
    private function fetchAndCombineDataFromYears(string $scopeId, array $years): array
    {
        $combinedData = [];
        $baseEntity = null;

        foreach ($years as $year) {
            try {
                $exchangeRequest = \App::$http
                    ->readGetResult(
                        '/warehouse/requestscope/' . $scopeId . '/' . $year . '/',
                        ['groupby' => 'day']
                    )
                    ->getEntity();

                // Use the first successfully fetched entity as the base
                if ($baseEntity === null) {
                    $baseEntity = $exchangeRequest;
                }

                // Combine data from all years
                if (isset($exchangeRequest->data) && is_array($exchangeRequest->data)) {
                    $combinedData = array_merge($combinedData, $exchangeRequest->data);
                }
            } catch (Exception $exception) {
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
    private function filterDataByDateRange(array $data, string $fromDate, string $toDate): array
    {
        $filteredData = [];
        foreach ($data as $row) {
            if ($row[3] >= $fromDate && $row[3] <= $toDate) {
                $filteredData[] = $row;
            }
        }
        return $filteredData;
    }

    /**
     * Create filtered exchange request with updated properties
     */
    private function createFilteredExchangeRequest(
        $exchangeRequestFull,
        array $filteredData,
        string $fromDate,
        string $toDate
    ): mixed {
        $exchangeRequest = clone $exchangeRequestFull;
        $exchangeRequest->data = $filteredData;

        if (!isset($exchangeRequest->period)) {
            $exchangeRequest->period = 'day';
        }

        $exchangeRequest->firstDay = (new Day())->setDateTime(new DateTime($fromDate));
        $exchangeRequest->lastDay = (new Day())->setDateTime(new DateTime($toDate));

        if (!empty($filteredData)) {
            $exchangeRequest = $exchangeRequest
                ->toGrouped($this->groupfields, $this->hashset)
                ->withRequestsSum()
                ->withAverage('processingtime');

            if (is_array($exchangeRequest->data)) {
                $locale = \App::$supportedLanguages[\App::$locale]['locale'] ?? 'de_DE';
                $collator = new \Collator($locale);
                uksort($exchangeRequest->data, static function ($itemA, $itemB) use ($collator) {
                    return $collator->compare($itemA, $itemB);
                });
            }

            return $exchangeRequest;
        }

        return $exchangeRequest->toHashed();
    }

    /**
     * Prepare download arguments for request report
     */
    public function prepareDownloadArgs(
        array $args,
        mixed $exchangeRequest,
        ?array $dateRange,
        array $selectedScopes = []
    ): array {
        $args['category'] = 'requestscope';

        if ($dateRange) {
            $args['period'] = $dateRange['from'] . '_' . $dateRange['to'];
        }

        if (!empty($selectedScopes)) {
            $args['selectedScopes'] = $selectedScopes;
        }

        if ($exchangeRequest && count($exchangeRequest->data)) {
            $args['reports'][] = $exchangeRequest;
        }

        return $args;
    }
}

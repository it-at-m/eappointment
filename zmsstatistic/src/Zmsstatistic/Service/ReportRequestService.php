<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Service;

use BO\Zmsentities\Day;
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
     * Extract selected scope IDs from request parameters
     */
    public function extractSelectedScopes(array $scopes): array
    {
        if (!empty($scopes)) {
            $validScopes = array_filter($scopes, function ($scopeId) {
                return is_numeric($scopeId) && $scopeId > 0;
            });

            if (!empty($validScopes)) {
                return array_map('intval', $validScopes);
            }
        }

        return [];
    }

    /**
     * Extract and validate date range from request parameters
     */
    public function extractDateRange(?string $fromDate, ?string $toDate): ?array
    {
        if ($fromDate && $toDate && $this->isValidDateFormat($fromDate) && $this->isValidDateFormat($toDate)) {
            return [
                'from' => $fromDate,
                'to' => $toDate
            ];
        }

        return null;
    }

    /**
     * Get exchange request data based on date range or period
     */
    public function getExchangeRequestData(string $scopeId, ?array $dateRange, array $args): mixed
    {
        if ($dateRange) {
            return $this->getExchangeRequestForDateRange($scopeId, $dateRange);
        } elseif (isset($args['period'])) {
            return $this->getExchangeRequestForPeriod($scopeId, $args['period']);
        }

        return null;
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
            $years = $this->getYearsForDateRange($fromDate, $toDate);
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
     * Get all years that need to be fetched for a date range
     */
    private function getYearsForDateRange(string $fromDate, string $toDate): array
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
     * Validate if the given string is a valid date format (YYYY-MM-DD)
     */
    public function isValidDateFormat(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime && $dateTime->format('Y-m-d') === $date;
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

    /**
     * Get the totals array for calculations
     */
    public function getTotals(): array
    {
        return $this->totals;
    }
}

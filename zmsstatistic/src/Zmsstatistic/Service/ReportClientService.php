<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Service;

use BO\Zmsentities\Day;
use DateTime;
use Exception;

class ReportClientService
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
     * Get exchange client data based on date range or period
     */
    public function getExchangeClientData(string $scopeId, ?array $dateRange, array $args): mixed
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
    public function getExchangeClientForDateRange(string $scopeId, array $dateRange): mixed
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

            return $this->createFilteredExchangeClient($combinedData['entity'], $filteredData, $fromDate, $toDate);
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Get exchange client data for a specific period (legacy functionality)
     */
    public function getExchangeClientForPeriod(string $scopeId, string $period): mixed
    {
        try {
            return \App::$http
                ->readGetResult('/warehouse/clientscope/' . $scopeId . '/' . $period . '/')
                ->getEntity()
                ->withCalculatedTotals($this->totals, 'date')
                ->toHashed();
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Get client period data for the current scope
     */
    public function getClientPeriod(string $scopeId): mixed
    {
        try {
            return \App::$http
                ->readGetResult('/warehouse/clientscope/' . $scopeId . '/')
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
                $exchangeClient = \App::$http
                    ->readGetResult(
                        '/warehouse/clientscope/' . $scopeId . '/' . $year . '/',
                        ['groupby' => 'day']
                    )
                    ->getEntity();

                // Use the first successfully fetched entity as the base
                if ($baseEntity === null) {
                    $baseEntity = $exchangeClient;
                }

                // Combine data from all years
                if (isset($exchangeClient->data) && is_array($exchangeClient->data)) {
                    $combinedData = array_merge($combinedData, $exchangeClient->data);
                }
            } catch (Exception $exception) {
                // Continue with other years - don't fail completely if one year is missing
            }
        }

        usort($combinedData, static function ($a, $b) {
            return strcmp($a[1] ?? '', $b[1] ?? '');
        });

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
            if ($row[1] >= $fromDate && $row[1] <= $toDate) {
                $filteredData[] = $row;
            }
        }
        return $filteredData;
    }

    /**
     * Create filtered exchange client with updated properties
     */
    private function createFilteredExchangeClient(
        $exchangeClientFull,
        array $filteredData,
        string $fromDate,
        string $toDate
    ): mixed {
        $exchangeClient = clone $exchangeClientFull;
        $exchangeClient->data = $filteredData;

        if (!isset($exchangeClient->period)) {
            $exchangeClient->period = 'day';
        }

        $exchangeClient->firstDay = (new Day())->setDateTime(new DateTime($fromDate));
        $exchangeClient->lastDay = (new Day())->setDateTime(new DateTime($toDate));

        if (!empty($filteredData)) {
            return $exchangeClient
                ->withCalculatedTotals($this->totals, 'date')
                ->toHashed();
        }

        return $exchangeClient->toHashed();
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
     * Prepare download arguments for client report
     */
    public function prepareDownloadArgs(
        array $args,
        mixed $exchangeClient,
        ?array $dateRange,
        array $selectedScopes = []
    ): array {
        $args['category'] = 'clientscope';

        if ($dateRange) {
            $args['period'] = $dateRange['from'] . '_' . $dateRange['to'];
        }

        if (!empty($selectedScopes)) {
            $args['selectedScopes'] = $selectedScopes;
        }

        if ($exchangeClient && count($exchangeClient->data)) {
            $args['reports'][] = $exchangeClient;
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

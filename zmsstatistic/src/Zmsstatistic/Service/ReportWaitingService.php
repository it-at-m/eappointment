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

class ReportWaitingService
{
    protected $hashset = [
        'waitingcount',
        'waitingtime',
        'waitingcalculated',
        'waitingcount_termin',
        'waitingtime_termin',
        'waitingcalculated_termin',
        'waytime',
        'waytime_termin',
    ];

    protected $groupfields = [
        'date',
        'hour'
    ];

    /**
     * Get exchange waiting data based on date range or period
     */
    public function getExchangeWaitingData(string $scopeId, ?array $dateRange, array $args): mixed
    {
        if ($dateRange) {
            return $this->getExchangeWaitingForDateRange($scopeId, $dateRange);
        } elseif (isset($args['period'])) {
            return $this->getExchangeWaitingForPeriod($scopeId, $args['period']);
        }

        return null;
    }

    /**
     * Get exchange waiting data for a specific date range
     */
    public function getExchangeWaitingForDateRange(string $scopeId, array $dateRange): mixed
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

            return $this->createFilteredExchangeWaiting($combinedData['entity'], $filteredData, $fromDate, $toDate);
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Get exchange waiting data for a specific period (legacy functionality)
     */
    public function getExchangeWaitingForPeriod(string $scopeId, string $period): mixed
    {
        try {
            $exchangeWaiting = \App::$http
                ->readGetResult('/warehouse/waitingscope/' . $scopeId . '/' . $period . '/')
                ->getEntity()
                ->toGrouped($this->groupfields, $this->hashset)
                ->withMaxByHour($this->hashset)
                ->withMaxAndAverageFromWaitingTime();

            // Apply extra ReportHelper processing
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime_termin');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime_termin');

            return $exchangeWaiting;
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Get waiting period data for the current scope
     */
    public function getWaitingPeriod(string $scopeId): mixed
    {
        try {
            return \App::$http
                ->readGetResult('/warehouse/waitingscope/' . $scopeId . '/')
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
                $exchangeWaiting = \App::$http
                    ->readGetResult(
                        '/warehouse/waitingscope/' . $scopeId . '/' . $year . '/',
                        ['groupby' => 'day']
                    )
                    ->getEntity();

                if ($baseEntity === null) {
                    $baseEntity = $exchangeWaiting;
                }

                if (isset($exchangeWaiting->data) && is_array($exchangeWaiting->data)) {
                    $combinedData = array_merge($combinedData, $exchangeWaiting->data);
                }
            } catch (Exception $exception) {
                // continue with other years
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
            if ($row[1] >= $fromDate && $row[1] <= $toDate) {
                $filteredData[] = $row;
            }
        }
        return $filteredData;
    }

    /**
     * Create filtered exchange waiting with updated properties
     */
    private function createFilteredExchangeWaiting(
        $exchangeWaitingFull,
        array $filteredData,
        string $fromDate,
        string $toDate
    ): mixed {
        $exchangeWaiting = clone $exchangeWaitingFull;
        $exchangeWaiting->data = $filteredData;

        if (!isset($exchangeWaiting->period)) {
            $exchangeWaiting->period = 'day';
        }

        $exchangeWaiting->firstDay = (new Day())->setDateTime(new DateTime($fromDate));
        $exchangeWaiting->lastDay = (new Day())->setDateTime(new DateTime($toDate));

        if (!empty($filteredData)) {
            $exchangeWaiting = $exchangeWaiting
                ->toGrouped($this->groupfields, $this->hashset)
                ->withMaxByHour($this->hashset)
                ->withMaxAndAverageFromWaitingTime();

            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime_termin');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime_termin');

            return $exchangeWaiting;
        }

        return $exchangeWaiting->toHashed();
    }

    /**
     * Prepare download arguments for waiting report
     */
    public function prepareDownloadArgs(
        array $args,
        mixed $exchangeWaiting,
        ?array $dateRange,
        array $selectedScopes = []
    ): array {
        $args['category'] = 'waitingscope';

        if ($dateRange) {
            $args['period'] = $dateRange['from'] . '_' . $dateRange['to'];
        }

        if (!empty($selectedScopes)) {
            $args['selectedScopes'] = $selectedScopes;
        }

        if ($exchangeWaiting && count($exchangeWaiting->data)) {
            $args['reports'][] = $exchangeWaiting;
        }

        return $args;
    }
}

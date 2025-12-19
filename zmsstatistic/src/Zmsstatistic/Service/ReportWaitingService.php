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
        'waitingcount_total',
        'waitingtime_total',
        'waytime_total',
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
        }

        return isset($args['period'])
            ? $this->getExchangeWaitingForPeriod($scopeId, $args['period'])
            : null;
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
            $combinedData = $this->fetchAndCombineDataFromYears($scopeId, $years, $fromDate, $toDate);

            if (empty($combinedData['data'])) {
                return null;
            }

            return $this->createFilteredExchangeWaiting(
                $combinedData['entity'],
                $combinedData['data'],
                $fromDate,
                $toDate
            );
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
                ->toGrouped($this->groupfields, $this->hashset);

            $exchangeWaiting = ReportHelper::withTotalCustomers($exchangeWaiting);

            $exchangeWaiting = $exchangeWaiting
                ->withMaxByHour($this->hashset)
                ->withMaxAndAverageFromWaitingTime();

            // Apply extra ReportHelper processing
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime_termin');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime_termin');

            // per-date max/avg for total
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime_total');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime_total');

            // global max/avg for total
            $exchangeWaiting = ReportHelper::withGlobalMaxAndAverage($exchangeWaiting, 'waitingtime_total');
            $exchangeWaiting = ReportHelper::withGlobalMaxAndAverage($exchangeWaiting, 'waytime_total');


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
    private function fetchAndCombineDataFromYears(string $scopeId, array $years, string $fromDate, string $toDate): array
    {
        $combinedData = [];
        $baseEntity = null;

        foreach ($years as $year) {
            try {
                $exchangeWaiting = \App::$http
                    ->readGetResult(
                        '/warehouse/waitingscope/' . $scopeId . '/' . $year . '/',
                        [
                            'groupby' => 'day',
                            'fromDate' => $fromDate,
                            'toDate' => $toDate
                        ]
                    )
                    ->getEntity();

                if (isset($exchangeWaiting->data) && is_array($exchangeWaiting->data)) {
                    $combinedData = array_merge($combinedData, $exchangeWaiting->data);
                }

                if ($baseEntity === null) {
                    unset($exchangeWaiting->data);
                    $baseEntity = $exchangeWaiting;
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
     * Create filtered exchange waiting with updated properties
     */
    private function createFilteredExchangeWaiting(
        $exchangeWaitingBasic,
        array $filteredData,
        string $fromDate,
        string $toDate
    ): mixed {
        $exchangeWaiting = $exchangeWaitingBasic;
        $exchangeWaiting->data = $filteredData;

        if (!isset($exchangeWaiting->period)) {
            $exchangeWaiting->period = 'day';
        }

        $exchangeWaiting->firstDay = (new Day())->setDateTime(new DateTime($fromDate));
        $exchangeWaiting->lastDay = (new Day())->setDateTime(new DateTime($toDate));

        if (!empty($filteredData)) {
            $exchangeWaiting = $exchangeWaiting
                ->toGrouped($this->groupfields, $this->hashset);

            $exchangeWaiting = ReportHelper::withTotalCustomers($exchangeWaiting);

            $exchangeWaiting = $exchangeWaiting->withMaxByHour($this->hashset)
                ->withMaxAndAverageFromWaitingTime();

            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime_termin');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime_termin');

            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waitingtime_total');
            $exchangeWaiting = ReportHelper::withMaxAndAverage($exchangeWaiting, 'waytime_total');
            $exchangeWaiting = ReportHelper::withGlobalMaxAndAverage($exchangeWaiting, 'waitingtime_total');
            $exchangeWaiting = ReportHelper::withGlobalMaxAndAverage($exchangeWaiting, 'waytime_total');


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

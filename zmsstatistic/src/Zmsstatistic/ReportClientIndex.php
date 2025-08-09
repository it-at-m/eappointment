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

        $exchangeClient = null;
        $dateRange = null;
        
        // Check for date range parameters
        $fromDate = $validator->getParameter('from')->isString()->getValue();
        $toDate = $validator->getParameter('to')->isString()->getValue();
        
        if ($fromDate && $toDate) {
            if ($this->isValidDateFormat($fromDate) && $this->isValidDateFormat($toDate)) {
                $dateRange = [
                    'from' => $fromDate,
                    'to' => $toDate
                ];
                $year = substr($fromDate, 0, 4);

                try {
                    // Fetch the whole year grouped by day
                    $exchangeClientFull = \App::$http
                        ->readGetResult('/warehouse/clientscope/' . $scopeId . '/' . $year . '/', ['groupby' => 'day'])
                        ->getEntity();

                    error_log("Filtering from: " . $fromDate . " to: " . $toDate);
                    error_log("Total data rows before filtering: " . count($exchangeClientFull->data));

                    $filteredData = [];
                    foreach ($exchangeClientFull->data as $row) {
                        if ($row[1] >= $fromDate && $row[1] <= $toDate) {
                            $filteredData[] = $row;
                        }
                    }

                    error_log("Total data rows after filtering: " . count($filteredData));

                    // Clone entity, replace data, calculate totals and convert to hash format
                    $exchangeClient = clone $exchangeClientFull;
                    $exchangeClient->data = $filteredData;
                    
                    // Ensure period is set for download functionality
                    if (!isset($exchangeClient->period)) {
                        $exchangeClient->period = 'day';
                    }
                    
                    // Update firstDay and lastDay to reflect the actual filtered date range
                    $exchangeClient->firstDay = (new \BO\Zmsentities\Day())->setDateTime(new \DateTime($fromDate));
                    $exchangeClient->lastDay = (new \BO\Zmsentities\Day())->setDateTime(new \DateTime($toDate));
                    
                    $exchangeClient = $exchangeClient
                        ->withCalculatedTotals($this->totals, 'date')
                        ->toHashed();

                } catch (\Exception $exception) {
                    error_log("Exception in exchangeClientFull: " . $exception->getMessage());
                }
            }
        } elseif (isset($args['period'])) {
            // Existing period functionality (backward compatibility)
            try {
                $exchangeClient = \App::$http
                    ->readGetResult('/warehouse/clientscope/' . $scopeId . '/' . $args['period'] . '/')
                    ->getEntity()
                    ->withCalculatedTotals($this->totals, 'date')
                    ->toHashed();
            } catch (\Exception $exception) {
                // do nothing
            }
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
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
     * 
     * @param string $date
     * @return bool
     */
    private function isValidDateFormat($date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }
}

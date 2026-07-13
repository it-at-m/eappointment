<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi\Helper;

use BO\Mellon\Validator;

class CalendarFromQuery
{
    /**
     * @return array{
     *     startDate: string,
     *     endDate: string,
     *     officeIds: array<int|string>,
     *     serviceIds: array<int|string>,
     *     serviceCounts: array<int|string>,
     *     providerSource: string|null,
     *     requestSource: string|null
     * }
     */
    public static function getParamsFromRequest(): array
    {
        $startDate = Validator::param('startDate')->isString()->getValue();
        $endDate = Validator::param('endDate')->isString()->getValue();
        $officeIds = Validator::param('officeId')->isString()->getValue();
        $serviceIds = Validator::param('serviceId')->isString()->getValue();
        $serviceCounts = Validator::param('serviceCount')->isString()->setDefault('')->getValue();

        if (!$startDate || !$endDate || !$officeIds || !$serviceIds) {
            throw new \BO\Zmsapi\Exception\Calendar\InvalidFirstDay(
                'startDate, endDate, officeId and serviceId are required'
            );
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'officeIds' => array_values(array_filter(array_map('trim', explode(',', $officeIds)))),
            'serviceIds' => array_map('trim', explode(',', $serviceIds)),
            'serviceCounts' => $serviceCounts !== ''
                ? array_map('trim', explode(',', $serviceCounts))
                : [],
            'providerSource' => Validator::param('providerSource')->isString()->getValue() ?: null,
            'requestSource' => Validator::param('requestSource')->isString()->getValue() ?: null,
        ];
    }
}

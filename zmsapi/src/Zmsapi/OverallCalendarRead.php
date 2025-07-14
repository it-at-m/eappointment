<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\OverallCalendar as CalendarQuery;
use DateTimeImmutable;
use DateTimeInterface;

class OverallCalendarRead extends BaseController
{
    private function buildCalendar(array $rows, int $defaultSeats = 1): array
    {
        $calendar = [];
        $lastSlotInfo = [];

        foreach ($rows as $row) {
            $dateKey  = (new DateTimeImmutable($row['time']))->format('Y-m-d');
            $timeKey  = (new DateTimeImmutable($row['time']))->format('H:i');
            $scopeKey = (int)$row['scope_id'];
            $seatNo   = (int)$row['seat'];

            $day   =& $calendar[$dateKey];
            $scope =& $day['scopes'][$scopeKey];
            $time  =& $scope['times'][$timeKey]['seats'];

            $day['date']        = (new DateTimeImmutable($dateKey))->getTimestamp();
            $scope['id']        = $scopeKey;
            $scope['name']      = $row['scope_name'];
            $scope['shortName'] = $row['scope_short'];
            $scope['maxSeats']  = max($scope['maxSeats'] ?? 0, $seatNo, $defaultSeats);

            if ($row['status'] === 'termin') {
                if ($row['slots'] !== null) {
                    $time[$seatNo] = [
                        'seatNo'   => $seatNo,
                        'status'   => 'termin',
                        'processId'=> (int) $row['process_id'],
                        'slots'    => (int) $row['slots'],
                    ];
                    $lastSlotInfo["$scopeKey|$seatNo"] = [
                        'processId' => (int) $row['process_id'],
                        'openSlots' => (int) $row['slots'] - 1,
                    ];
                } else {
                    $time[$seatNo] = [
                        'seatNo' => $seatNo,
                        'status' => 'skip'
                    ];
                    $info = $lastSlotInfo["$scopeKey|$seatNo"] ?? null;
                    if ($info && --$info['openSlots'] <= 0) {
                        unset($lastSlotInfo["$scopeKey|$seatNo"]);
                    } else {
                        $lastSlotInfo["$scopeKey|$seatNo"] = $info;
                    }
                }
            } elseif ($row['status'] === 'cancelled') {
                $time[$seatNo] = [
                    'seatNo' => $seatNo,
                    'status' => 'cancelled'
                ];
                unset($lastSlotInfo["$scopeKey|$seatNo"]);
            } else {
                $time[$seatNo] = [
                    'seatNo' => $seatNo,
                    'status' => 'open'
                ];
            }
        }

        foreach ($calendar as &$day) {
            foreach ($day['scopes'] as &$scope) {
                foreach ($scope['times'] as $timeKey => $slotInfo) {
                    ksort($slotInfo['seats']);
                    $scope['times'][$timeKey] = [
                        'name'  => $timeKey,
                        'seats' => array_values($slotInfo['seats']),
                    ];
                }
                $scope['times'] = array_values($scope['times']);
            }
            $day['scopes'] = array_values($day['scopes']);
        }
        uksort($calendar, fn($a, $b) => strcmp($a, $b));

        return ['days' => array_values($calendar)];
    }

    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request))->checkRights('useraccount');
        $scopeIdCsv = Validator::param('scopeIds')
            ->isString()->isMatchOf('/^\d+(,\d+)*$/')->assertValid()->getValue();
        $scopeIds   = array_map('intval', explode(',', $scopeIdCsv));

        $dateFrom   = Validator::param('dateFrom')->isDate('Y-m-d')->assertValid()->getValue();
        $dateUntil  = Validator::param('dateUntil')->isDate('Y-m-d')->assertValid()->getValue();
        $updateAfter = Validator::param('updateAfter')->isDatetime()->setDefault(null)->getValue();

        $flatRows = (new CalendarQuery())->readSlots(
            $scopeIds,
            $dateFrom,
            $dateUntil,
            $updateAfter
        );

        $structured = $this->buildCalendar($flatRows);

        $msg           = Response\Message::create($request);
        $msg->data     = $structured;
        $msg->meta->rows = count($flatRows);

        $response = Render::withLastModified(
            $response,
            (new DateTimeImmutable())->getTimestamp(),
            '0'
        );
        return Render::withJson(
            $response,
            $msg->setUpdatedMetaData(),
            $msg->getStatuscode()
        );
    }
}

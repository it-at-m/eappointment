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

        foreach ($rows as $r) {
            $dateKey  = (new DateTimeImmutable($r['time']))->format('Y-m-d');
            $timeKey  = (new DateTimeImmutable($r['time']))->format('H:i');
            $scopeKey = (int)$r['scope_id'];
            $seatNo   = (int)$r['seat'];

            $day   =& $calendar[$dateKey];
            $scope =& $day['scopes'][$scopeKey];
            $time  =& $scope['times'][$timeKey]['seats'];

            $day['date']            = (new DateTimeImmutable($dateKey))->getTimestamp();
            $scope['id']            = $scopeKey;
            $scope['name']          = '';
            $scope['maxSeats']      = max($scope['maxSeats'] ?? 0, $seatNo, $defaultSeats);
            $time[$seatNo]['init']  = true;

            if ($r['status'] === 'termin') {
                if ($r['slots'] === null) {
                    $time[$seatNo] = ['status' => 'skip'];
                } else {
                    $time[$seatNo] = [
                        'status'    => 'termin',
                        'processId' => (int)$r['process_id'],
                        'slots'     => (int)$r['slots'],
                    ];
                    $lastSlotInfo["$scopeKey|$seatNo"] = [
                        'processId' => (int)$r['process_id'],
                        'openSlots' => (int)$r['slots'] - 1,
                    ];
                }
            } else {
                $info = $lastSlotInfo["$scopeKey|$seatNo"] ?? null;
                if ($info && $info['openSlots'] > 0) {
                    $time[$seatNo] = ['status' => 'skip'];
                    $lastSlotInfo["$scopeKey|$seatNo"]['openSlots']--;
                } else {
                    $time[$seatNo] = ['status' => 'open'];
                    unset($lastSlotInfo["$scopeKey|$seatNo"]);
                }
            }
        }

        foreach ($calendar as &$day) {
            foreach ($day['scopes'] as &$scope) {
                foreach ($scope['times'] as $timeKey => $slotInfo) {

                    for ($s = 1; $s <= $scope['maxSeats']; $s++) {
                        if (!isset($slotInfo['seats'][$s])) {
                            $slotInfo['seats'][$s] = ['status' => 'open'];
                        }
                    }
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

        return ['days' => array_values($calendar)];
    }

    public function readResponse(
        \Psr\Http\Message\RequestInterface  $request,
        \Psr\Http\Message\ResponseInterface $response,
        array                               $args
    ) {
        $scopeIdCsv = Validator::param('scopeIds')
            ->isString()->isMatchOf('/^\d+(,\d+)*$/')->assertValid()->getValue();
        $scopeIds   = array_map('intval', explode(',', $scopeIdCsv));

        $dateFrom   = Validator::param('dateFrom')->isDate('Y-m-d')->assertValid()->getValue();
        $dateUntil  = Validator::param('dateUntil')->isDate('Y-m-d')->assertValid()->getValue();
        $updateAfter= Validator::param('updateAfter')->isDatetime()->setDefault(null)->getValue();

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
